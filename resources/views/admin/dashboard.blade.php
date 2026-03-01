<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>

<body>
<header class="top-header">
    <div class="container top-header-inner">
        <div class="greeting-left">Welcome, {{ auth()->user()->name }}!</div>
        <!-- admin does not need request leave button -->
    </div>
</header>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Admin</h2>
            <ul class="nav-links">
                <li>
                    <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) class="active" @endif>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.leave.requests') }}" @if(request()->routeIs('admin.leave.requests')) class="active" @endif>
                        Requests
                        @if($pendingLeaves > 0)
                            <span class="badge">{{ $pendingLeaves }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>
                        Accounts
                        @if($pendingUsersCount > 0)
                            <span class="badge">{{ $pendingUsersCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.add.account') }}" @if(request()->routeIs('admin.add.account')) class="active" @endif>
                        Add Account
                    </a>
                </li>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <!-- Greeting removed (now in fixed header) -->

        <!-- Admin Stats Section -->
        <div class="stats-grid">
            <a href="{{ route('admin.approved_accounts') }}" class="card-link">
            <div class="card green">
                <h3>
                    <i class="fas fa-users"></i>
                    {{ $totalEmployees }}
                </h3>
                <p>Total Employees</p>
                <i class="fas fa-arrow-right arrow-icon"></i>
            </div>
            </a>

            <a href="{{ route('admin.leave.requests') }}" class="card-link">
            <div class="card yellow">
                <h3>
                    <i class="fas fa-hourglass-half"></i>
                    {{ $pendingLeaves }}
                </h3>
                <p>Pending Leave Requests</p>
                <i class="fas fa-arrow-right arrow-icon"></i>
            </div>
            </a>

            <a href="{{ route('admin.approved.this.month') }}" class="card-link">
            <div class="card blue">
                <h3>
                    <i class="fas fa-check-circle"></i>
                    {{ $approvedThisMonth }}
                </h3>
                <p>Approved Requests <br>(This Month)</p>
                <i class="fas fa-arrow-right arrow-icon"></i>
            </div>
            </a>

            <div class="card-wrapper">
            <div class="card red">
                <h3>
                    <i class="fas fa-times-circle"></i>
                    {{ $rejectedLeaves }}
                </h3>
                <p>Rejected Leaves</p>
            </div>
            </div>

            <div class="card-wrapper">
            <div class="card purple">
                <h3>
                    <i class="fas fa-calendar-alt"></i>
                    {{ $totalThisYear }}
                </h3>
                <p>Total Requests <br>(This Year)</p>
            </div>
            </div>

        </div>

            </div>
        </div>
    </div>

</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>