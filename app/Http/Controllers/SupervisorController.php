<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    // Supervisor dashboard
    public function dashboard()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        $leaveTypes = \App\Models\LeaveType::all();

        // Ensure balances exist
        foreach ($leaveTypes as $type) {
            \App\Models\LeaveBalance::firstOrCreate(
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

        $balances = \App\Models\LeaveBalance::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();

        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->whereYear('start_date', $currentYear)
            ->with(['leaveType', 'sessions'])
            ->get();

        // Count pending supervisions
        $pendingCount = LeaveRequest::where('status', 'pending_supervisor')
            ->whereIn('user_id', User::where('supervisor_id', $user->id)->pluck('id'))
            ->count();

        return view('supervisor.dashboard', compact('balances', 'leaveTypes', 'leaveRequests', 'pendingCount'));
    }

    // View pending employee requests awaiting supervisor approval
    public function leaveRequests()
    {
        $supervisor = Auth::user();

        $pendingRequests = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'pending_supervisor')
            ->whereIn('user_id', User::where('supervisor_id', $supervisor->id)->pluck('id'))
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingCount = $pendingRequests->count();

        // Pass leave types to Blade for modal
        $leaveTypes = LeaveType::all();

        return view('supervisor.leave-requests', compact('pendingRequests', 'pendingCount', 'leaveTypes'));
    }

    // Approve/Reject employee leave request
    public function decision(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'supervisor_remarks' => 'required|string|max:500',
        ]);

        $supervisor = Auth::user();

        DB::transaction(function () use ($request, $id, $supervisor) {
            $leave = LeaveRequest::where('id', $id)
                ->where('status', 'pending_supervisor')
                ->firstOrFail();

            // Verify supervisor is responsible for this employee
            if ($leave->user->supervisor_id !== $supervisor->id) {
                abort(403, 'Unauthorized');
            }

            if ($request->status === 'approved') {
                // Forward to manager
                $leave->update([
                    'status' => 'supervisor_approved_pending_manager',
                    'supervisor_remarks' => $request->supervisor_remarks,
                    'supervisor_approved_at' => now(),
                ]);
            } elseif ($request->status === 'rejected') {
                // Reject directly
                $balance = \App\Models\LeaveBalance::where('user_id', $leave->user_id)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('year', date('Y'))
                    ->first();

                if ($balance) {
                    $balance->decrement('pending_credits', $leave->total_days);
                }

                $leave->update([
                    'status' => 'rejected',
                    'supervisor_remarks' => $request->supervisor_remarks,
                ]);
            }
        });

        $msg = $request->status === 'approved' ? 'forwarded to manager' : 'rejected';
        return back()->with('success', "Leave request {$msg} successfully.");
    }

    // Show supervisor's own leave history
    public function history()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        $allRequests = LeaveRequest::where('user_id', $user->id)
            ->with(['leaveType', 'sessions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $leaveTypes = \App\Models\LeaveType::all();

        // Count pending supervisions for sidebar badge
        $pendingCount = LeaveRequest::where('status', 'pending_supervisor')
            ->whereIn('user_id', User::where('supervisor_id', $user->id)->pluck('id'))
            ->count();

        return view('supervisor.leave-history', compact('allRequests', 'currentYear', 'leaveTypes', 'pendingCount'));
    }

    // Get leave sessions for modal
    public function getLeaveRequestSessions($id)
    {
        $supervisor = Auth::user();

        $leave = LeaveRequest::with('sessions')
            ->where('status', 'pending_supervisor')
            ->findOrFail($id);

        // Verify supervisor is responsible
        if ($leave->user->supervisor_id !== $supervisor->id) {
            abort(403);
        }

        return response()->json([
            'start_date' => $leave->start_date->toDateString(),
            'end_date' => $leave->end_date->toDateString(),
            'sessions' => $leave->sessions->map(function ($session) {
                return [
                    'date' => $session->date->toDateString(),
                    'session' => $session->session,
                ];
            })->toArray(),
        ]);
    }

    // View team members (employees assigned to this supervisor)
    public function viewTeam()
    {
        $supervisor = Auth::user();
        
        // Get count for sidebar badge
        $pendingCount = LeaveRequest::where('status', 'pending_supervisor')
            ->whereIn('user_id', User::where('supervisor_id', $supervisor->id)->pluck('id'))
            ->count();

        // Get direct reports (employees assigned to this supervisor)
        $employees = User::where('supervisor_id', $supervisor->id)
            ->where('status', 'approved')
            ->with(['manager'])
            ->orderBy('name', 'asc')
            ->get();

        // Get the manager for this supervisor
        $manager = $supervisor->manager;

        // Get leave types for leave request modal
        $leaveTypes = LeaveType::all();

        return view('supervisor.team', compact('employees', 'pendingCount', 'supervisor', 'manager', 'leaveTypes'));
    }
}
