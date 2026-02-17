<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    // Show manager dashboard
    public function index()
    {
        $manager = Auth::user();
        $currentYear = date('Y');

        // Get subordinates (team members)
        $teamMembers = User::where('manager_id', $manager->id)
            ->where('status', 'approved')
            ->count();

        // Pending leave requests from team
        $pendingRequests = LeaveRequest::where('status', 'pending_manager')
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->count();

        // Leave requests this month
        $thisMonthRequests = LeaveRequest::whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->whereYear('start_date', $currentYear)
            ->whereMonth('start_date', Carbon::now()->month)
            ->count();

        return view('manager.dashboard', compact(
            'teamMembers',
            'pendingRequests',
            'thisMonthRequests'
        ));
    }

    // Show team employees for this manager
    public function employees()
    {
        $manager = Auth::user();
        $employees = User::where('manager_id', $manager->id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('manager.employees', compact('employees'));
    }

    // Show pending leave requests for manager's team
    public function leaveRequests()
    {
        $manager = Auth::user();
        $pendingRequests = LeaveRequest::where('status', 'pending_manager')
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->with(['user', 'leaveType'])
            ->orderBy('created_at', 'asc')
            ->get();

        $requestCount = $pendingRequests->count();

        return view('manager.leave-requests', compact('pendingRequests', 'requestCount'));
    }

    // View a specific leave request
    public function requestDetail($id)
    {
        $manager = Auth::user();
        $request = LeaveRequest::where('id', $id)
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->with(['user', 'leaveType'])
            ->firstOrFail();

        return view('manager.request-detail', compact('request'));
    }

    // Manager approves and forwards request to Admin
    public function forward(Request $request, $id)
    {
        $validated = $request->validate([
            'manager_remarks' => 'required|string|max:500',
        ]);

        $manager = Auth::user();
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('status', 'pending_manager')
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->firstOrFail();

        // Update leave request status to pending_admin and manager remarks
        $leaveRequest->update([
            'status' => 'pending_admin',
            'manager_id' => $manager->id,
            'manager_remarks' => $validated['manager_remarks'],
            'forwarded_at' => now(),
        ]);

        return back()->with('success', 'Leave request forwarded to admin for final approval.');
    }

    // Manager rejects request
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'manager_remarks' => 'required|string|max:500',
        ]);

        $manager = Auth::user();
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('status', 'pending_manager')
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->firstOrFail();

        DB::transaction(function () use ($leaveRequest, $validated, $manager) {
            // Return pending credits to balance
            $balance = $leaveRequest->user->leaveBalances()
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', date('Y'))
                ->first();

            if ($balance) {
                $balance->decrement('pending_credits', $leaveRequest->total_days);
            }

            // Update leave request status to rejected
            $leaveRequest->update([
                'status' => 'rejected',
                'manager_id' => $manager->id,
                'manager_remarks' => $validated['manager_remarks'],
            ]);
        });

        return back()->with('success', 'Leave request rejected successfully.');
    }

    // Show approved leave requests
    public function approvedRequests()
    {
        $manager = Auth::user();
        $approvedRequests = LeaveRequest::where('status', 'approved')
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->with(['user', 'leaveType'])
            ->orderBy('start_date', 'asc')
            ->get();

        $requestCount = $approvedRequests->count();

        return view('manager.approved-requests', compact('approvedRequests', 'requestCount'));
    }

    // Manager submit their own leave request (goes directly to admin)
    public function submitLeave(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'start_session' => 'nullable|in:full,morning,afternoon',
            'end_session'   => 'nullable|in:full,morning,afternoon',
            'reason'        => 'required|string|max:500',
        ]);

        $manager = Auth::user();

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $start_session = $validated['start_session'] ?? 'full';
        $end_session   = $validated['end_session'] ?? 'full';

        // Prevent past dates
        if ($start->lt(Carbon::today()) || $end->lt(Carbon::today())) {
            return back()->with('error', 'You cannot request leave for past dates.');
        }

        // Calculate total_days considering half-days
        $totalDays = $this->calculateTotalDays($start, $end, $start_session, $end_session);

        // Check overlapping leaves
        $overlap = LeaveRequest::where('user_id', $manager->id)
            ->whereIn('status', ['pending_admin', 'approved'])
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'You already have a leave during these dates.');
        }

        $currentYear = date('Y');

        $balance = $manager->leaveBalances()
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $currentYear)
            ->first();

        if (!$balance || $balance->available_credits < $totalDays) {
            return back()->with('error', 'Insufficient leave credits.');
        }

        // Store leave request
        DB::transaction(function () use ($validated, $manager, $totalDays, $start, $end, $start_session, $end_session) {
            LeaveRequest::create([
                'user_id'       => $manager->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'start_session' => $start_session,
                'end_session'   => $end_session,
                'total_days'    => $totalDays,
                'reason'        => $validated['reason'],
                'status'        => 'pending_admin', // Manager request goes directly to admin
            ]);

            // Lock and update balance
            $balance = $manager->leaveBalances()
                ->where('leave_type_id', $validated['leave_type_id'])
                ->where('year', date('Y'))
                ->lockForUpdate()
                ->first();

            $balance->increment('pending_credits', $totalDays);
        });

        return back()->with('success', 'Leave request submitted and sent to admin for approval.');
    }

    // Helper function to calculate total days with half-day sessions
    private function calculateTotalDays($start, $end, $startSession, $endSession)
    {
        if ($start->equalTo($end)) {
            if ($startSession === 'full' && $endSession === 'full') {
                return 1;
            }

            if ($startSession !== 'full' && $endSession !== 'full') {
                return 1;
            }

            return 0.5;
        }

        $daysBetween = $start->diffInDays($end) + 1;
        $total = $daysBetween;

        if ($startSession !== 'full') {
            $total -= 0.5;
        }

        if ($endSession !== 'full') {
            $total -= 0.5;
        }

        return $total;
    }
}
