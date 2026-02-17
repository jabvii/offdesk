<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class AdminController extends Controller
{
public function index()
{
    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;

    // Total Users (excluding soft deleted)
    $totalUsers = User::count();

    // Total Employees (non-admin & non-manager & Active)
    $totalEmployees = User::where('role', 'employee')
        ->where('status', 'approved')
        ->count();

    // Total Managers
    $totalManagers = User::where('role', 'manager')
        ->where('status', 'approved')
        ->count();

    // Pending Users (awaiting approval)
    $pendingUsersCount = User::where('status', 'pending')->count();

    // Pending Leave Requests for Admin (only pending_admin requests)
    $pendingLeaves = LeaveRequest::where('status', 'pending_admin')->count();

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
        'totalUsers',
        'totalEmployees',
        'totalManagers',
        'pendingUsersCount',
        'pendingLeaves',
        'approvedThisMonth',
        'rejectedLeaves',
        'totalThisYear'
    ));
}

    // Page where admin views ALL pending leave requests for review and final approval
    public function leaveRequests()
    {
        $pendingRequests = LeaveRequest::with(['user', 'leaveType', 'manager'])
            ->where('status', 'pending_admin')
            ->orderBy('created_at', 'asc')
            ->get();
        $pendingCount = $pendingRequests->count();
        return view('admin.leave-requests', compact('pendingRequests', 'pendingCount'));
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
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_decision' => $request->status,
            ]);
        });

        $msg = $request->status === 'approved' ? 'approved' : 'rejected';
        return back()->with('success', "Leave request {$msg} successfully.");
    }

    public function accounts()
    {
        // Pending users for approval
        $pendingUsers = User::where('status', 'pending')->get();
        $allUsers = User::where('status', '!=', 'pending')->get();

        $pendingUsersCount = $pendingUsers->count();
        $pendingCount = LeaveRequest::where('status', 'pending_admin')->count();

        return view('admin.accounts', compact(
            'pendingUsers',
            'allUsers',
            'pendingUsersCount',
            'pendingCount'
        ));
    }

    public function approvedEmployees()
    {
        $pendingUsers = User::where('status', 'pending')->get();
        $pendingUsersCount = $pendingUsers->count();
        $pendingCount = LeaveRequest::where('status', 'pending_admin')->count();

        $employees = User::where('status', 'approved')
            ->get();

        return view('admin.approved_accounts', compact(
            'employees',
            'pendingUsersCount',
            'pendingCount'
        ));
    }

    // Approve a pending user
    public function approveUser(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', "Cannot approve yourself.");

        $oldStatus = $user->status;
        $user->update([
            'status' => 'approved',
            'is_approved' => true,
        ]);

        return back()->with('success', "{$user->name} approved!");
    }

    public function rejectUser(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', "Cannot reject yourself.");

        $oldStatus = $user->status;
        $user->update([
            'status' => 'rejected',
            'is_approved' => false,
        ]);

        return back()->with('success', "{$user->name} rejected!");
    }

}