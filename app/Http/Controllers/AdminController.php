<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // Pending Users (awaiting approval)
    $pendingUsersCount = User::where('is_admin', false)
        ->where('status', 'pending')
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
        'pendingUsersCount',
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
        // Pending users for the "Pending Accounts" table
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
        $pendingCount = LeaveRequest::where('status', 'pending')->count();

        $employees = User::where('is_admin', 0)
                        ->where('status', 'approved')
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

        $user->update([
            'status' => 'approved',
            'is_approved' => true,
        ]);

        return back()->with('success', "{$user->name} approved!");
    }

    public function rejectUser(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', "Cannot reject yourself.");

        $user->update([
            'status' => 'rejected',
            'is_approved' => false,
        ]);

        return back()->with('success', "{$user->name} rejected!");
    }


}