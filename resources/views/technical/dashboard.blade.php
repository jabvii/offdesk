<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Technical Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #2c3e50;
        }
        .stat-card h3 {
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
        }
        .quick-actions {
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .quick-actions h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .action-btn {
            padding: 12px 16px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            display: block;
        }
        .action-btn:hover {
            background: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .audit-table {
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .audit-table h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .no-logs {
            text-align: center;
            color: #7f8c8d;
            padding: 40px 20px;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDESK TECH</h2>
            <ul class="nav-links">
                <li><a href="{{ route('technical.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('technical.accounts') }}">Manage Accounts</a></li>
                <li><a href="{{ route('technical.role.management') }}">Role Management</a></li>
                <li><a href="{{ route('technical.audit.log') }}">Audit Log</a></li>
                <li><a href="{{ route('technical.managers.list') }}">View Managers</a></li>
                <li><a href="{{ route('technical.statistics') }}">Statistics</a></li>
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

            <!-- Greeting -->
            <div class="dashboard-greeting">
                <span>Welcome, {{ auth()->user()->name }}!</span>
            </div>

            <!-- Statistics Cards -->
            <div class="stat-cards">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="stat-value">{{ $totalUsers }}</div>
                </div>
                <div class="stat-card">
                    <h3>Pending Approval</h3>
                    <div class="stat-value">{{ $pendingUsers }}</div>
                </div>
                <div class="stat-card">
                    <h3>Approved Users</h3>
                    <div class="stat-value">{{ $approvedUsers }}</div>
                </div>
                <div class="stat-card">
                    <h3>Rejected Users</h3>
                    <div class="stat-value">{{ $rejectedUsers }}</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-grid">
                    <a href="{{ route('technical.accounts') }}" class="action-btn">Manage Accounts</a>
                    <a href="{{ route('technical.role.management') }}" class="action-btn">Role Management</a>
                    <a href="{{ route('technical.audit.log') }}" class="action-btn">Audit Log</a>
                    <a href="{{ route('technical.managers.list') }}" class="action-btn">View Managers</a>
                    <a href="{{ route('technical.statistics') }}" class="action-btn">Statistics</a>
                </div>
            </div>

            <!-- Recent Audit Log -->
            <div class="audit-table">
                <h3>Recent Audit Log</h3>
                @if($recentAuditLogs->count() > 0)
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Subject</th>
                                    <th>Performed By</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAuditLogs as $log)
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->auditable?->name ?? 'N/A' }}</td>
                                        <td>{{ $log->performedBy->name }}</td>
                                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-logs">
                        <p>No audit logs yet</p>
                    </div>
                @endif
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
