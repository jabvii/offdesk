<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - View Team</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager/team.css') }}">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Manager</h2>
            <ul class="nav-links">
                <li><a href="{{ route('manager.dashboard') }}">Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
                <li>
                    <a href="{{ route('manager.leave.requests') }}">
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('manager.team') }}" class="active">View Team</a></li>
                <li><a href="{{ route('manager.leave.history') }}">Leave History</a></li>
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
            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <div class="team-container">
                <div class="team-header">
                    <h2>{{ $manager->department }} Department Team</h2>
                    <p class="team-subtitle">Total: {{ $supervisors->count() + $employees->count() }} members</p>
                </div>

                <!-- Supervisors Section -->
                @if($supervisors->count() > 0)
                <div class="team-section">
                    <h3 class="section-title">
                        <span class="role-badge supervisor">Supervisors</span>
                        <span class="member-count">({{ $supervisors->count() }})</span>
                    </h3>
                    <div class="team-table-wrapper">
                        <table class="team-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Reports To</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supervisors as $member)
                                <tr>
                                    <td>
                                        <div class="member-name">
                                            <span class="name">{{ $member->name }}</span>
                                            @if($member->is_supervisor)
                                                <span class="supervisor-badge">Can Supervise</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        @if($member->manager)
                                            {{ $member->manager->name }}
                                        @else
                                            <span class="no-manager">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Employees Section -->
                <div class="team-section">
                    <h3 class="section-title">
                        <span class="role-badge employee">Employees</span>
                        <span class="member-count">({{ $employees->count() }})</span>
                    </h3>
                    @if($employees->count() > 0)
                    <div class="team-table-wrapper">
                        <table class="team-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Supervisor</th>
                                    <th>Manager</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td>
                                        @if($member->supervisor)
                                            {{ $member->supervisor->name }}
                                        @else
                                            <span class="no-supervisor">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->manager)
                                            {{ $member->manager->name }}
                                        @else
                                            <span class="no-manager">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="no-members">No employees in this department yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}
</script>
</body>
</html>
