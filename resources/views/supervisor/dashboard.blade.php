<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Supervisor Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supervisor/dashboard.css') }}">

</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Supervisor</h2>
            <ul class="nav-links">
                <li><a href="{{ route('supervisor.dashboard') }}" @if(request()->routeIs('supervisor.dashboard')) class="active" @endif>Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
                <li>
                    <a href="{{ route('supervisor.leave.requests') }}" @if(request()->routeIs('supervisor.leave.requests')) class="active" @endif>
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('supervisor.team') }}" @if(request()->routeIs('supervisor.team')) class="active" @endif>View Team</a></li>
                <li><a href="{{ route('supervisor.leave.history') }}" @if(request()->routeIs('supervisor.leave.history')) class="active" @endif>Leave History</a></li>
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

            <!-- Leave Balance Cards -->
            <div class="balance-cards">
                @foreach($balances->take(3) as $balance)
                <div class="balance-card">
                    <h3 class="{{ strtolower($balance->leaveType->code) }}">{{ $balance->leaveType->name }}</h3>
                    <div class="balance-info">
                        <div class="balance-row">
                            <span class="balance-label">Leave Credits</span>
                            <span class="balance-value">{{ $balance->total_credits }} days</span>
                        </div>
                        <div class="balance-row">
                            <span class="balance-label">Used Credits</span>
                            <span class="balance-value">{{ $balance->used_credits }}</span>
                        </div>
                        <div class="balance-row">
                            <span class="balance-label">Pending Requests</span>
                            <span class="balance-value">{{ $balance->pending_credits }}</span>
                        </div>
                        <div class="balance-row">
                            <span class="balance-label">Available</span>
                            <span class="balance-value available">{{ $balance->available_credits }} days</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Calendar Section -->
            <div class="calendar-section">
                <div class="calendar-header">
                    <h2>{{ now()->year }} Calendar</h2>
                </div>

                @php
                $leaveDays = [];
                foreach ($leaveRequests as $request) {
                    $status = strtolower($request->status);
                    if(!in_array($status, ['approved','pending'])) continue;

                    $start = \Carbon\Carbon::parse($request->start_date);
                    $end = \Carbon\Carbon::parse($request->end_date);

                    // Create a map of sessions by date
                    $sessionMap = [];
                    foreach ($request->sessions as $session) {
                        $sessionMap[$session->date->toDateString()] = $session->session;
                    }

                    while ($start->lte($end)) {
                        $dateStr = $start->toDateString();
                        $session = $sessionMap[$dateStr] ?? 'whole_day';

                        $leaveDays[$start->month][$start->day][] = [
                            'id' => $request->id,
                            'type' => strtolower($request->leaveType->code ?? ''),
                            'status' => $status,
                            'session' => $session,
                            'start_date' => $request->start_date,
                            'end_date' => $request->end_date,
                        ];

                        $start->addDay();
                    }
                }
                @endphp

                <div class="calendar-table-wrapper">
                    <table class="calendar-table">
                        <thead>
                        <tr>
                            <th>Month</th>
                            @for ($day = 1; $day <= 31; $day++)
                                <th>{{ $day }}</th>
                            @endfor
                        </tr>
                        </thead>
                        <tbody>
                        @for ($month = 1; $month <= 12; $month++)
                            @php
                                $date = now()->startOfYear()->month($month);
                                $daysInMonth = $date->daysInMonth;
                            @endphp
                            <tr>
                                <td class="month-name">{{ $date->format('F') }}</td>
                                @for ($day = 1; $day <= 31; $day++)
                                    @if ($day <= $daysInMonth)
                                        @php 
                                            $dayLeaves = $leaveDays[$month][$day] ?? [];
                                            $currentDate = \Carbon\Carbon::create(now()->year, $month, $day);
                                            $isWeekend = $currentDate->isWeekend();
                                            $dayName = $currentDate->format('l');
                                        @endphp
                                        <td class="calendar-day {{ count($dayLeaves) ? 'has-leave' : '' }} {{ $isWeekend ? 'weekend' : '' }}" title="{{ $dayName }}">
                                            @if($isWeekend)
                                                <div class="weekend-indicator" title="Weekend"></div>
                                            @else
                                                @foreach($dayLeaves as $leave)
                                                    <div class="leave-dot {{ $leave['type'] }} {{ $leave['session'] }}">
                                                        {{ $leave['session'] !== 'whole_day' ? strtoupper($leave['session'][0]) : '' }}
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    @else
                                        <td class="calendar-day disabled"></td>
                                    @endif
                                @endfor
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Your Leave Requests -->
            <div class="leave-requests-list">
                <h2>Your Leave Requests</h2>
                @forelse($leaveRequests->sortByDesc('created_at')->take(5) as $request)
                    <div class="leave-request-item">
                        <div class="leave-request-info">
                            <h4 class="{{ strtolower($request->leaveType->code) }}">{{ $request->leaveType->name }}</h4>

                            <p class="pdark">
                                {{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
                                - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                ({{ $request->total_days }} day{{ $request->total_days > 1 ? 's' : '' }})
                            </p>

                            <p>{{ $request->reason }}</p>

                            @if($request->status !== 'pending_admin' && $request->admin_remarks)
                                <p class="pdark"><strong>Admin Remarks:</strong> {{ $request->admin_remarks }}</p>
                            @endif
                        </div>

                        <div class="leave-request-actions">
                            <span class="status-badge status-{{ $request->status }}">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span>
                            @if(in_array($request->status, ['pending_manager', 'pending_admin', 'supervisor_approved_pending_manager']))
                                <form action="{{ route('leave.cancel', $request->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p id="no-requests-message">No leave requests yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal for viewing leave sessions -->
<div id="leaveSessionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Leave Details</h2>
            <button class="modal-close" onclick="closeLeaveSessionModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p><strong>Date Range:</strong> <span id="dateRange"></span></p>
            <div class="table-responsive">
                <table class="simple-table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Session</th>
                    </tr>
                    </thead>
                    <tbody id="leaveSessionsTableBody">
                    <!-- Sessions populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeLeaveSessionModal()">Close</button>
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
