<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>OffDesk - Accounts</title>

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/accounts.css') }}">

</head>
<body>
<div class="dashboard-container">

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk GUESS</h2>
            <ul class="nav-links">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li>
                    <a href="{{ route('admin.leave.requests') }}">
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('admin.accounts') }}" class="active">Accounts</a></li>
            </ul>
        </div>

        <div class="nav-bottom">
            <ul class="nav-links">
                <li>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to logout?')">
                        @csrf
                        <button type="submit" class="nav-link logout-link">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main -->
    <div class="main-content">
        <div class="container">

            <div class="dashboard-greeting">
                <span>Welcome, {{ auth()->user()->name }}!</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <h2 class="dblue">Pending Accounts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Registered At</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="status-badge status-pending">Pending</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" style="display:inline;" onsubmit="return confirm('Approve this user?')">
                                    @csrf
                                    <button type="submit" class="admin-action-btn">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" style="display:inline;" onsubmit="return confirm('Reject this user?')">
                                    @csrf
                                    <button type="submit" class="admin-action-btn">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No pending accounts 🎉</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h2 class="dblue" style="margin-top:40px;">All Accounts</h2>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Registered At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($user->status === 'approved')
                                    <span class="status-badge status-approved">Approved</span>
                                @elseif($user->status === 'rejected')
                                    <span class="status-badge status-rejected">Rejected</span>
                                @else
                                    <span class="status-badge status-pending">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

</body>
</html> 