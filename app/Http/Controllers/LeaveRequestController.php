<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestSession;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
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
            ->with(['leaveType', 'sessions'])
            ->get();

        return view('employee.dashboard', compact('balances', 'leaveTypes', 'leaveRequests'));
    }
 
    // Store a new leave request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'daily_sessions' => 'required|array',
            'daily_sessions.*' => 'required|in:whole_day,morning,afternoon',
            'reason'        => 'required|string|max:500',
        ]);

        $user = Auth::user();

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        // Prevent past dates
        if ($start->lt(Carbon::today()) || $end->lt(Carbon::today())) {
            return back()->with('error', 'You cannot request leave for past dates.');
        }

        // Validate that daily_sessions exists and is not empty
        if (empty($validated['daily_sessions']) || !is_array($validated['daily_sessions'])) {
            return back()->with('error', 'No sessions data provided.');
        }

        // Calculate total_days from sessions (this is the source of truth)
        $totalDays = $this->calculateTotalDaysFromSessions($validated['daily_sessions']);

        if ($totalDays <= 0) {
            return back()->with('error', 'Invalid session configuration.');
        }

        // Count weekdays in the date range to validate session count
        $currentDate = $start->copy();
        $weekdayCount = 0;
        while ($currentDate->lte($end)) {
            if (!$currentDate->isWeekend()) {
                $weekdayCount++;
            }
            $currentDate->addDay();
        }

        // Validate that the number of sessions matches the number of weekdays
        if (count($validated['daily_sessions']) !== $weekdayCount) {
            return back()->with('error', 'Session count mismatch. Expected ' . $weekdayCount . ' sessions for weekdays only.');
        }

        \Log::info('Leave request validation', [
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'sessions_count' => count($validated['daily_sessions']),
            'sessions_data' => $validated['daily_sessions'],
            'calculated_total_days' => $totalDays,
        ]);

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

        // Store leave request with sessions
        DB::transaction(function () use ($validated, $user, $totalDays, $balance, $start, $end) {
            // Determine status based on user role and assignments
            // Employees go to supervisor first, supervisors go to manager, managers go directly to admin
            if ($user->isManager()) {
                $status = 'pending_admin';
            } elseif ($user->isSupervisor()) {
                // Supervisor goes to manager, or directly to admin if no manager assigned
                $status = $user->manager_id ? 'pending_manager' : 'pending_admin';
            } else {
                // Employee goes to supervisor first, or manager if no supervisor, or admin if neither
                if ($user->supervisor_id) {
                    $status = 'pending_supervisor';
                } elseif ($user->manager_id) {
                    $status = 'pending_manager';
                } else {
                    $status = 'pending_admin';
                }
            }

            $leaveRequest = LeaveRequest::create([
                'user_id'       => $user->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'total_days'    => $totalDays,
                'reason'        => $validated['reason'],
                'status'        => $status,
            ]);

            // Create daily sessions
            $currentDate = $start->copy();
            $sessionIndex = 0;
            while ($currentDate->lte($end)) {
                // Skip weekends — frontend doesn't submit sessions for them
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                LeaveRequestSession::create([
                    'leave_request_id' => $leaveRequest->id,
                    'date'             => $currentDate->toDateString(),
                    'session'          => $validated['daily_sessions'][$sessionIndex],
                ]);

                $currentDate->addDay();
                $sessionIndex++;
            }

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

        $cancellableStatuses = [
            'pending_supervisor',
            'pending_manager',
            'pending_admin',
            'supervisor_approved_pending_manager'
        ];

        if (!in_array($leaveRequest->status, $cancellableStatuses)) {
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

    // Show all leave request history for current user
    public function history()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        $allRequests = LeaveRequest::where('user_id', $user->id)
            ->with(['leaveType', 'sessions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $leaveTypes = LeaveType::all();

        // Return appropriate view based on user role
        if ($user->isManager()) {
            return view('manager.leave-history', compact('allRequests', 'currentYear', 'leaveTypes'));
        } elseif ($user->isSupervisor()) {
            return view('supervisor.leave-history', compact('allRequests', 'currentYear', 'leaveTypes'));
        }

        return view('employee.leave-history', compact('allRequests', 'currentYear', 'leaveTypes'));
    }

    // View department members (for employees to see their team structure)
    public function viewDepartment()
    {
        $user = Auth::user();
        
        // Get all members in the same department
        $departmentMembers = User::where('department', $user->department)
            ->where('id', '!=', $user->id)
            ->where('is_admin', false)
            ->where('status', 'approved')
            ->with(['supervisor', 'manager'])
            ->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Separate by role for display
        $managers = $departmentMembers->where('role', 'manager');
        $supervisors = $departmentMembers->where('role', 'supervisor');
        $employees = $departmentMembers->where('role', 'employee');

        // Get user's own supervisor and manager for highlighting
        $mySupervisor = $user->supervisor;
        $myManager = $user->manager;

        // Get leave types for leave request modal
        $leaveTypes = LeaveType::all();

        return view('employee.department', compact(
            'managers', 
            'supervisors', 
            'employees', 
            'user', 
            'mySupervisor', 
            'myManager',
            'leaveTypes'
        ));
    }

    // Helper function to calculate total days from sessions
    private function calculateTotalDaysFromSessions($dailySessions): float
    {
        $totalDays = 0;

        foreach ($dailySessions as $session) {
            if ($session === 'whole_day') {
                $totalDays += 1;
            } else {
                // morning or afternoon
                $totalDays += 0.5;
            }
        }

        return $totalDays;
    }
}