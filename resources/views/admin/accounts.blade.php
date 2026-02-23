<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>OffDesk - Accounts</title>

<link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/accounts.css') }}">

</head>
<body>
<div class="dashboard-container">

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk GUESS</h2>
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
                <li><a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>Accounts</a></li>
                <li><a href="{{ route('admin.add.account') }}" @if(request()->routeIs('admin.add.account')) class="active" @endif>Add Account</a></li>
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

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <h2 class="dblue">All Accounts</h2>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department }}</td>
                            <td>
                                <span class="role-badge">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No accounts yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>

</body>
</html> 