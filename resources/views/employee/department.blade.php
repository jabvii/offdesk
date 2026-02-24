<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - My Department</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/employee/department.css') }}">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Employee</h2>
            <ul class="nav-links">
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
                <li><a href="{{ route('employee.department') }}" class="active">My Department</a></li>
                <li><a href="{{ route('employee.leave.history') }}">Leave History</a></li>
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

            <div class="department-container">
                <div class="department-header">
                    <h2>{{ $user->department }} Department</h2>
                    <p class="department-subtitle">{{ $managers->count() + $supervisors->count() + $employees->count() + 1 }} members (including you)</p>
                </div>

                <!-- Manager Section -->
                @if($managers->count() > 0)
                <div class="team-section">
                    <h3 class="section-title">
                        <span class="role-badge manager">Manager</span>
                        <span class="member-count">({{ $managers->count() }})</span>
                    </h3>
                    <div class="team-table-wrapper">
                        <table class="team-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($managers as $member)
                                <tr class="{{ $myManager && $myManager->id === $member->id ? 'highlight-row' : '' }}">
                                    <td>
                                        {{ $member->name }}
                                        @if($myManager && $myManager->id === $member->id)
                                            <span class="your-tag">Your Manager</span>
                                        @endif
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

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
                                    <th>Manager</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supervisors as $member)
                                <tr class="{{ $mySupervisor && $mySupervisor->id === $member->id ? 'highlight-row' : '' }}">
                                    <td>
                                        {{ $member->name }}
                                        @if($mySupervisor && $mySupervisor->id === $member->id)
                                            <span class="your-tag">Your Supervisor</span>
                                        @endif
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

                <!-- Fellow Employees Section -->
                <div class="team-section">
                    <h3 class="section-title">
                        <span class="role-badge employee">Fellow Employees</span>
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
                    <p class="no-members">No other employees in this department yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="modal" id="leaveRequestModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Submit Leave Request</h2>
        </div>
        <form action="{{ route('leave.store') }}" method="POST" id="leaveRequestForm">
            @csrf
            <div class="form-group">
                <label for="leave_type_id">Leave Type</label>
                <select name="leave_type_id" id="leave_type_id" required>
                    <option value="">Select Leave Type</option>
                    @forelse($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @empty
                        <option value="">No leave types available</option>
                    @endforelse
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" required>
            </div>

            <!-- Daily Sessions Table -->
            <div id="sessionsTableContainer" style="display: none; margin-top: 20px;">
                <label>Select Session for Each Day</label>
                <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Session</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsTableBody">
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
<script>
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

function openModal() {
    document.getElementById('leaveRequestModal').classList.add('active');
}

function closeModal() {
    document.getElementById('leaveRequestModal').classList.remove('active');
    document.getElementById('leaveRequestForm').reset();
    document.getElementById('sessionsTableContainer').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const openLeaveModalLink = document.getElementById('openLeaveModalLink');
    if (openLeaveModalLink) {
        openLeaveModalLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    const endDateInput = document.getElementById('end_date');
    const startDateInput = document.getElementById('start_date');
    const sessionsTableContainer = document.getElementById('sessionsTableContainer');
    const sessionsTableBody = document.getElementById('sessionsTableBody');
    const leaveRequestForm = document.getElementById('leaveRequestForm');

    function generateSessionsTable() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (!startDate || !endDate) {
            sessionsTableContainer.style.display = 'none';
            return;
        }

        const start = new Date(startDate + 'T00:00:00');
        const end = new Date(endDate + 'T00:00:00');

        if (start > end) {
            alert('Start date must be before or equal to end date');
            endDateInput.value = '';
            sessionsTableContainer.style.display = 'none';
            return;
        }

        sessionsTableBody.innerHTML = '';
        let currentDate = new Date(start);
        const dateFormat = (date) => date.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });

        while (currentDate <= end) {
            const dateStr = currentDate.toISOString().split('T')[0];
            const formattedDate = dateFormat(currentDate);
            const dayOfWeek = currentDate.getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

            const row = document.createElement('tr');

            if (isWeekend) {
                row.classList.add('weekend-row');
                row.style.backgroundColor = '#f0f0f0';
                row.style.color = '#999';
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>
                        <span class="weekend-label" style="font-style: italic; color: #aaa;">
                            Weekend
                        </span>
                    </td>
                `;
            } else {
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>
                        <select name="daily_sessions[]" class="session-select" data-date="${dateStr}" required>
                            <option value="whole_day">Whole Day</option>
                            <option value="morning">Morning (8:00am-12:00pm)</option>
                            <option value="afternoon">Afternoon (1:00pm-5:30pm)</option>
                        </select>
                    </td>
                `;
            }

            sessionsTableBody.appendChild(row);
            currentDate.setDate(currentDate.getDate() + 1);
        }

        sessionsTableContainer.style.display = 'block';
    }

    endDateInput.addEventListener('change', generateSessionsTable);
    startDateInput.addEventListener('change', generateSessionsTable);

    leaveRequestForm.addEventListener('submit', function(e) {
        const sessionSelects = document.querySelectorAll('.session-select');

        if (sessionSelects.length === 0) {
            e.preventDefault();
            alert('Please select start and end dates first');
            return false;
        }
    });
});

window.onclick = function(event) {
    const modal = document.getElementById('leaveRequestModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
</body>
</html>
