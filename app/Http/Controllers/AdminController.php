<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;


class AdminController extends Controller
{
public function index()
{
    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;

    // Total Employees (non-admin & active)
    $totalEmployees = User::where('is_admin', false)
        ->where('status', 'approved')
        ->count();

    // Pending Leave Requests
    $pendingLeaves = LeaveRequest::whereIn('status', ['pending_admin'])->count();

    // Approved Leaves (This Month)
    $approvedThisMonth = LeaveRequest::where('status', 'approved')
        ->whereYear('start_date', $currentYear)
        ->whereMonth('start_date', $currentMonth)
        ->count();

    // Rejected Leaves
    $rejectedLeaves = LeaveRequest::where('status', 'rejected')->count();

    // Total Leave Requests (This Year)
    $totalThisYear = LeaveRequest::whereYear('start_date', $currentYear)->count();

    return view('admin.dashboard', compact(
        'totalEmployees',
        'pendingLeaves',
        'approvedThisMonth',
        'rejectedLeaves',
        'totalThisYear'
    ));
}

    // Page where admin views ALL pending leave requests
    public function leaveRequests()
    {
        $pendingRequests = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'pending_admin')
            ->orderBy('created_at', 'asc')
            ->get();
        $pendingCount = $pendingRequests->count();
        return view('admin.leave-requests', compact('pendingRequests', 'pendingCount'));
    }

    // Get leave request sessions as JSON
    public function getLeaveRequestSessions($id)
    {
        $leave = LeaveRequest::with('sessions')
            ->where('status', 'pending_admin')
            ->findOrFail($id);

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

    /*  Process approve or reject leave requests with admin remarks. */
    public function decision(Request $request, $id)
    {  
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $id) {

            $leave = LeaveRequest::where('id', $id)
                ->where('status', 'pending_admin')
                ->firstOrFail();

            $balance = LeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', now()->year)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status === 'approved') {
                // pending → used
                $balance->decrement('pending_credits', $leave->total_days);
                $balance->increment('used_credits', $leave->total_days);
            } elseif ($request->status === 'rejected') {
                // return pending credits
                $balance->decrement('pending_credits', $leave->total_days);
            }

            // update leave request with status and remarks
            $leave->update([
                'status' => $request->status,
                'admin_remarks' => $request->admin_remarks,
            ]);
        });

        $msg = $request->status === 'approved' ? 'approved' : 'rejected';
        return back()->with('success', "Leave request {$msg} successfully.");
    }

    public function accounts()
    {
        // All user accounts
        $allUsers = User::where('is_admin', false)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $pendingCount = LeaveRequest::where('status', 'pending_admin')->count();

        return view('admin.accounts', compact(
            'allUsers',
            'pendingCount'
        ));
    }

    public function approvedEmployees()
    {   
        $pendingCount = LeaveRequest::where('status', 'pending_admin')->count();

        $employees = User::with(['supervisor', 'manager'])
                        ->where('is_admin', 0)
                        ->where('status', 'approved')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('admin.approved_accounts', compact(
            'employees',
            'pendingCount'
        ));
    }

    // Show add account form
    public function showAddAccount()
    {
        $managers = User::where('role', 'manager')
            ->where('status', 'approved')
            ->get();

        $supervisors = User::where('is_supervisor', true)
            ->where('status', 'approved')
            ->get();

        return view('admin.add-account', compact('managers', 'supervisors'));
    }

    // Store new account created by admin
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'department' => ['required', 'string', 'in:IT,Accounting,HR,Treasury,Sales,Planning,Visual,Engineering'],
            'role' => ['required', 'string', 'in:employee,supervisor,manager'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_supervisor' => ['nullable', 'boolean'],
        ]);

        // Handle role-specific assignments
        if ($validated['role'] === 'employee') {
            // Employee: needs both supervisor and manager
            if (!$validated['supervisor_id']) {
                $supervisor = User::where('department', $validated['department'])
                    ->where('is_supervisor', true)
                    ->where('status', 'approved')
                    ->first();
                if ($supervisor) {
                    $validated['supervisor_id'] = $supervisor->id;
                }
            }

            if (!$validated['manager_id']) {
                $manager = User::where('department', $validated['department'])
                    ->where('role', 'manager')
                    ->where('status', 'approved')
                    ->first();
                if ($manager) {
                    $validated['manager_id'] = $manager->id;
                }
            }
        } elseif ($validated['role'] === 'supervisor') {
            // Supervisor: needs a manager, can be under another supervisor
            if (!$validated['manager_id']) {
                $manager = User::where('department', $validated['department'])
                    ->where('role', 'manager')
                    ->where('status', 'approved')
                    ->first();
                if ($manager) {
                    $validated['manager_id'] = $manager->id;
                }
            }
            // Supervisors are marked as supervisors
            $validated['is_supervisor'] = true;
        } elseif ($validated['role'] === 'manager') {
            // Manager: optionally can supervise employees
            $validated['is_supervisor'] = $request->has('is_supervisor') ? true : false;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department' => $validated['department'],
            'role' => $validated['role'],
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'is_supervisor' => $validated['is_supervisor'] ?? false,
            'is_admin' => false,
            'status' => 'approved',  // Admin-created accounts are auto-approved
            'is_approved' => true,
        ]);

        $message = ucfirst($validated['role']) . " account for {$user->name} created successfully!";
        return redirect()->route('admin.accounts')->with('success', $message);
    }


}