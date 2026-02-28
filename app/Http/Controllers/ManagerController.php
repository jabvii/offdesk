<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    // Manager dashboard
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

        $pendingCount = LeaveRequest::whereIn('status', ['pending_supervisor', 'pending_manager', 'supervisor_approved_pending_manager'])
            ->whereIn('user_id', User::where('manager_id', $user->id)->pluck('id'))
            ->count();

        return view('manager.dashboard', compact('balances', 'leaveTypes', 'leaveRequests', 'pendingCount'));
    }

    // View pending employee requests awaiting manager approval
    public function leaveRequests()
    {
        $manager = Auth::user();

        $pendingRequests = LeaveRequest::with(['user', 'leaveType', 'supervisor'])
            ->whereIn('status', ['pending_supervisor', 'pending_manager', 'supervisor_approved_pending_manager'])
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingCount = $pendingRequests->count();

        // Pass leave types to Blade for modal
        $leaveTypes = LeaveType::all();

        return view('manager.leave-requests', compact('pendingRequests', 'pendingCount', 'leaveTypes'));
    }

    // Approve/Reject employee leave request
    public function decision(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_remarks' => 'required|string|max:500',
        ]);

        $manager = Auth::user();

        DB::transaction(function () use ($request, $id, $manager) {
            $leave = LeaveRequest::where('id', $id)
                ->whereIn('status', ['pending_manager', 'supervisor_approved_pending_manager'])
                ->firstOrFail();

            // Verify manager is responsible for this employee
            if ($leave->user->manager_id !== $manager->id) {
                abort(403, 'Unauthorized');
            }

            if ($request->status === 'approved') {
                // Forward to admin
                $leave->update([
                    'status' => 'pending_admin',
                    'manager_remarks' => $request->manager_remarks,
                    'manager_approved_at' => now(),
                    'forwarded_at' => now(),
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
                    'manager_remarks' => $request->manager_remarks,
                ]);
            }
        });

        $msg = $request->status === 'approved' ? 'forwarded to admin' : 'rejected';
        return back()->with('success', "Leave request {$msg} successfully.");
    }

    // Get leave sessions for modal
    public function getLeaveRequestSessions($id)
    {
        $manager = Auth::user();

        $leave = LeaveRequest::with('sessions')
            ->whereIn('status', ['pending_supervisor', 'pending_manager', 'supervisor_approved_pending_manager'])
            ->findOrFail($id);

        // Verify manager is responsible
        if ($leave->user->manager_id !== $manager->id) {
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

    // View team members (supervisors and employees in manager's department)
    public function viewTeam()
    {
        $manager = Auth::user();
        
        // Get count for sidebar badge
        $pendingCount = LeaveRequest::whereIn('status', ['pending_supervisor', 'pending_manager', 'supervisor_approved_pending_manager'])
            ->whereIn('user_id', User::where('manager_id', $manager->id)->pluck('id'))
            ->count();

        // Get all team members in the same department (excluding the manager themselves)
        $teamMembers = User::where('department', $manager->department)
            ->where('id', '!=', $manager->id)
            ->where('is_admin', false)
            ->where('status', 'approved')
            ->with(['supervisor', 'manager'])
            ->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Separate by role for display
        $supervisors = $teamMembers->where('role', 'supervisor');
        $employees = $teamMembers->where('role', 'employee');

        // Get leave types for leave request modal
        $leaveTypes = LeaveType::all();

        return view('manager.team', compact('supervisors', 'employees', 'pendingCount', 'manager', 'leaveTypes'));
    }

    // Assign supervisor to employee
    public function assignSupervisor(Request $request, User $employee)
    {
        $manager = Auth::user();
        // Only allow assignment within manager's department and to employees
        if ($employee->department !== $manager->department || $employee->role !== 'employee') {
            return redirect()->back()->with('error', 'Invalid employee selection.');
        }
        $request->validate([
            'supervisor_id' => 'nullable|exists:users,id',
        ]);
        $supervisorId = $request->input('supervisor_id');
        // Only allow supervisors from the same department
        if ($supervisorId) {
            $supervisor = User::where('id', $supervisorId)
                ->where('department', $manager->department)
                ->where('role', 'supervisor')
                ->first();
            if (!$supervisor) {
                return redirect()->back()->with('error', 'Invalid supervisor selection.');
            }
        }
        $employee->supervisor_id = $supervisorId ?: null;
        $employee->save();
        return redirect()->back()->with('success', 'Supervisor assigned successfully.');
    }

    // Bypass supervisor approval and allow manager to decide
    public function bypassSupervisor(Request $request, $id)
    {
        $manager = Auth::user();
        $leave = LeaveRequest::where('id', $id)
            ->where('status', 'pending_supervisor')
            ->firstOrFail();

        // Verify manager is responsible for this employee
        if ($leave->user->manager_id !== $manager->id) {
            abort(403, 'Unauthorized');
        }

        // Skip supervisor, set status to pending_manager
        $leave->update([
            'status' => 'pending_manager',
            'supervisor_remarks' => 'Bypassed by manager',
            'supervisor_approved_at' => now(),
        ]);

        return back()->with('success', 'Supervisor approval bypassed. You may now approve or reject this request.');
    }
}
