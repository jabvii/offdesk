<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    // Show user dashboard
    public function index()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        $leaveTypes = LeaveType::all();

        // Ensure balances exist
        foreach ($leaveTypes as $type) {
            LeaveBalance::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'leave_type_id' => $type->id,
                    'year' => $currentYear,
                ],
                [
                    'total_credits' => $type->max_days,
                    'used_credits' => 0,
                    'pending_credits' => 0,
                ]
            );
        }

        $balances = LeaveBalance::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();

        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->whereYear('start_date', $currentYear)
            ->with('leaveType')
            ->get();

        return view('dashboard', compact('balances', 'leaveTypes', 'leaveRequests'));
    }

    // Store a new leave request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'start_session' => 'nullable|in:full,morning,afternoon',
            'end_session'   => 'nullable|in:full,morning,afternoon',
            'reason'        => 'required|string|max:500',
        ]);

        $user = Auth::user();

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

        // Check overlapping leaves (approved or pending only)
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
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

        // Lock balance row
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $currentYear)
            ->lockForUpdate()
            ->firstOrFail();

        if ($balance->available_credits < $totalDays) {
            return back()->with('error', 'Insufficient leave credits.');
        }

        // Store leave request
        DB::transaction(function () use ($validated, $user, $totalDays, $balance, $start, $end, $start_session, $end_session) {
            LeaveRequest::create([
                'user_id'       => $user->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'start_session' => $start_session,
                'end_session'   => $end_session,
                'total_days'    => $totalDays,
                'reason'        => $validated['reason'],
                'status'        => 'pending',
            ]);

            $balance->increment('pending_credits', $totalDays);
        });
        return back()->with('success', 'Leave request submitted successfully.');
    }

    // Cancel a pending leave request
    public function cancel($id)
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        DB::transaction(function () use ($leaveRequest) {
            $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', date('Y'))
                ->first();

            if ($balance) {
                $balance->decrement('pending_credits', $leaveRequest->total_days);
            }

            $leaveRequest->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Leave request cancelled successfully.');
    }

    // Helper function to calculate total days with half-day sessions
    private function calculateTotalDays($start, $end, $startSession, $endSession)
    {
        if ($start->equalTo($end)) {
            // Same day logic
            if ($startSession === 'full' && $endSession === 'full') {
                return 1;
            }

            if ($startSession !== 'full' && $endSession !== 'full') {
                return 1; // morning → afternoon counts as full day
            }

            return 0.5;
        }

        $daysBetween = $start->diffInDays($end) + 1;

        $total = $daysBetween;

        // Adjust start day
        if ($startSession !== 'full') {
            $total -= 0.5;
        }

        // Adjust end day
        if ($endSession !== 'full') {
            $total -= 0.5;
        }

        return $total;
    }
}