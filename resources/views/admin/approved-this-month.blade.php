<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Approved Requests This Month</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/leave-requests.css') }}">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Admin</h2>
            <ul class="nav-links">
                <li><a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) class="active" @endif>Dashboard</a></li>
                <li>
                    <a href="{{ route('admin.leave.requests') }}" @if(request()->routeIs('admin.leave.requests')) class="active" @endif>
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>
                        Accounts
                        @if($pendingUsersCount ?? 0 > 0)
                            <span class="badge">{{ $pendingUsersCount ?? 0 }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('admin.add.account') }}" @if(request()->routeIs('admin.add.account')) class="active" @endif>Add Account</a></li>
            </ul>
        </div>
        <div class="nav-bottom">
            <ul class="nav-links">
                <li>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirmLogout()">
                        @csrf
                        <button type="submit" class="nav-link logout-link">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main content -->
    <div class="main-content">
        <div class="container">
            <h2 class="dblue">Approved Leave Requests - {{ $monthName }}</h2>
            <p style="color: #9ca3af; margin-bottom: 20px;">{{ $approvedRequests->count() }} approved request(s) this month</p>

            <div class="requests-table-wrapper">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Approved On</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedRequests as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}</td>
                            <td>{{ $leave->user->department }}</td>
                            <td>
                                <span class="leave-type-badge {{ strtolower(str_replace(' ', '_', $leave->leaveType->name)) }}">
                                    {{ $leave->leaveType->name }}
                                </span>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} 
                                @if($leave->start_date != $leave->end_date)
                                    → {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                @endif
                            </td>
                            <td>{{ $leave->total_days }}</td>
                            <td>{{ \Carbon\Carbon::parse($leave->updated_at)->format('M d, Y h:i A') }}</td>
                            <td>{{ $leave->admin_remarks ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">No approved leave requests this month</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}
</script>
</body>
</html>
