<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TechnicalController extends Controller
{
    // Show technical admin dashboard
    public function index()
    {
        $currentYear = date('Y');

        // Total users (excluding soft deleted)
        $totalUsers = User::count();

        // Pending users
        $pendingUsers = User::where('status', 'pending')->count();

        // Approved users
        $approvedUsers = User::where('status', 'approved')->count();

        // Rejected users
        $rejectedUsers = User::where('status', 'rejected')->count();

        // Leave requests this month
        $thisMonthRequests = LeaveRequest::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', Carbon::now()->month)
            ->count();

        // Recent audit logs
        $recentAuditLogs = AuditLog::with(['performedBy', 'auditable'])
            ->latest()
            ->limit(10)
            ->get();

        return view('technical.dashboard', compact(
            'totalUsers',
            'pendingUsers',
            'approvedUsers',
            'rejectedUsers',
            'thisMonthRequests',
            'recentAuditLogs'
        ));
    }

    // View all accounts sorted with details
    public function accounts(Request $request)
    {
        $sortBy = $request->get('sort_by', 'name');
        $status = $request->get('status', null);
        $role = $request->get('role', null);

        $query = User::query();

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by role
        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        // Sort
        if (in_array($sortBy, ['name', 'email', 'created_at', 'status', 'role'])) {
            $query->orderBy($sortBy, 'asc');
        }

        $users = $query->paginate(15);

        return view('technical.accounts', compact('users', 'sortBy', 'status', 'role'));
    }

    // View detailed account information
    public function accountDetail($id)
    {
        $user = User::with(['manager', 'subordinates', 'leaveRequests'])
            ->findOrFail($id);

        return view('technical.account-detail', compact('user'));
    }

    // Delete (soft delete) a user account
    public function deleteAccount(Request $request, $id)
    {
        $technical = Auth::user();
        $user = User::findOrFail($id);

        if ($user->id === $technical->id) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        // Soft delete user
        $user->delete();

        // Log the action
        AuditLog::create([
            'action' => 'user_deleted',
            'performed_by' => $technical->id,
            'auditable_id' => $user->id,
            'auditable_type' => User::class,
            'changes' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
            'remarks' => $request->input('remarks'),
        ]);

        return back()->with('success', "{$user->name}'s account has been deleted.");
    }

    // Approve a user account
    public function approveAccount(Request $request, $id)
    {
        $technical = Auth::user();
        $user = User::findOrFail($id);

        if ($user->id === $technical->id) {
            return back()->with('error', 'Cannot approve yourself.');
        }

        if ($user->status === 'approved') {
            return back()->with('error', 'User is already approved.');
        }

        $oldStatus = $user->status;
        $user->update([
            'status' => 'approved',
            'is_approved' => true,
        ]);

        // Log the action
        AuditLog::create([
            'action' => 'user_approved',
            'performed_by' => $technical->id,
            'auditable_id' => $user->id,
            'auditable_type' => User::class,
            'changes' => [
                'status' => ['from' => $oldStatus, 'to' => 'approved'],
            ],
            'remarks' => $request->input('remarks'),
        ]);

        return back()->with('success', "{$user->name} has been approved.");
    }

    // Reject a user account
    public function rejectAccount(Request $request, $id)
    {
        $technical = Auth::user();
        $user = User::findOrFail($id);

        if ($user->id === $technical->id) {
            return back()->with('error', 'Cannot reject yourself.');
        }

        if ($user->status === 'rejected') {
            return back()->with('error', 'User is already rejected.');
        }

        $oldStatus = $user->status;
        $user->update([
            'status' => 'rejected',
            'is_approved' => false,
        ]);

        // Log the action
        AuditLog::create([
            'action' => 'user_rejected',
            'performed_by' => $technical->id,
            'auditable_id' => $user->id,
            'auditable_type' => User::class,
            'changes' => [
                'status' => ['from' => $oldStatus, 'to' => 'rejected'],
            ],
            'remarks' => $request->input('remarks'),
        ]);

        return back()->with('success', "{$user->name} has been rejected.");
    }

    // View audit log
    public function auditLog(Request $request)
    {
        $action = $request->get('action', null);
        $startDate = $request->get('start_date', null);
        $endDate = $request->get('end_date', null);

        $query = AuditLog::with(['performedBy', 'auditable']);

        // Filter by action
        if ($action && $action !== 'all') {
            $query->where('action', $action);
        }

        // Filter by date range
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('technical.audit-log', compact('logs', 'action', 'startDate', 'endDate'));
    }

    // View all managers per department
    public function managersByDepartment()
    {
        $departments = [
            'IT', 'Accounting', 'HR', 'Treasury', 'Sales', 'Planning', 'Visual', 'Engineering'
        ];

        $managersByDept = [];

        foreach ($departments as $dept) {
            $managers = User::where('role', 'manager')
                ->where('department', $dept)
                ->where('status', 'approved')
                ->with('subordinates')
                ->get();

            $managersByDept[$dept] = $managers;
        }

        return view('technical.managers-list', compact('managersByDept'));
    }

    // View role management (change user roles)
    public function roleManagement(Request $request)
    {
        $sortBy = $request->get('sort_by', 'name');
        $role = $request->get('role', null);

        $query = User::where('status', 'approved');

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        if (in_array($sortBy, ['name', 'email', 'role', 'department'])) {
            $query->orderBy($sortBy, 'asc');
        }

        $users = $query->paginate(15);

        return view('technical.role-management', compact('users', 'sortBy', 'role'));
    }

    // Change user role
    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:employee,manager,admin,technical',
        ]);

        $technical = Auth::user();
        $user = User::findOrFail($id);

        if ($user->id === $technical->id) {
            return back()->with('error', 'Cannot change your own role.');
        }

        $oldRole = $user->role;
        $user->update([
            'role' => $validated['role'],
        ]);

        // Log the action
        AuditLog::create([
            'action' => 'user_role_changed',
            'performed_by' => $technical->id,
            'auditable_id' => $user->id,
            'auditable_type' => User::class,
            'changes' => [
                'role' => ['from' => $oldRole, 'to' => $validated['role']],
            ],
        ]);

        return back()->with('success', "{$user->name}'s role has been updated to {$validated['role']}.");
    }

    // View statistics
    public function statistics()
    {
        $currentYear = date('Y');

        // Total users by role
        $usersByRole = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        // Users by status
        $usersByStatus = User::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Approved users by department
        $usersByDept = User::selectRaw('department, COUNT(*) as count')
            ->where('status', 'approved')
            ->groupBy('department')
            ->get();

        // Leave requests by status this year
        $leavesByStatus = LeaveRequest::selectRaw('status, COUNT(*) as count')
            ->whereYear('start_date', $currentYear)
            ->groupBy('status')
            ->get();

        return view('technical.statistics', compact(
            'usersByRole',
            'usersByStatus',
            'usersByDept',
            'leavesByStatus'
        ));
    }
}
