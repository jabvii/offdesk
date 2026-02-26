<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Manager Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manager/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Manager</h2>
            <ul class="nav-links">
                <li><a href="{{ route('manager.dashboard') }}" @if(request()->routeIs('manager.dashboard')) class="active" @endif>Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
                <li>
                    <a href="{{ route('manager.leave.requests') }}" @if(request()->routeIs('manager.leave.requests')) class="active" @endif>
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('manager.team') }}" @if(request()->routeIs('manager.team')) class="active" @endif>View Team</a></li>
                <li><a href="{{ route('manager.leave.history') }}" @if(request()->routeIs('manager.leave.history')) class="active" @endif>Leave History</a></li>
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
                // Leave type code to acronym mapping
                $leaveAcronyms = [
                    'vacation' => 'VL',
                    'sick' => 'SL',
                    'emergency' => 'EL',
                    'paternity' => 'PAL',
                    'parental' => 'PRL',
                    'service_incentive' => 'SIL',
                ];

                // Pending statuses in the workflow
                $pendingStatuses = [
                    'pending_supervisor',
                    'pending_manager',
                    'pending_admin',
                    'supervisor_approved_pending_manager',
                ];

                $leaveDays = [];
                foreach ($leaveRequests as $request) {
                    $status = strtolower($request->status);
                    
                    // Determine display status: approved or pending
                    if ($status === 'approved') {
                        $displayStatus = 'approved';
                    } elseif (in_array($status, $pendingStatuses)) {
                        $displayStatus = 'pending';
                    } else {
                        continue; // Skip rejected, cancelled, etc.
                    }

                    $start = \Carbon\Carbon::parse($request->start_date);
                    $end = \Carbon\Carbon::parse($request->end_date);

                    // Get leave type acronym
                    $leaveCode = strtolower($request->leaveType->code ?? '');
                    $acronym = $leaveAcronyms[$leaveCode] ?? strtoupper(substr($leaveCode, 0, 2));

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
                            'type' => $leaveCode,
                            'acronym' => $acronym,
                            'status' => $displayStatus,
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
                                                    @php
                                                        $dotClass = $leave['status'];
                                                        if ($leave['status'] === 'approved' && $leave['type'] === 'emergency') {
                                                            $dotClass = 'approved emergency';
                                                        }
                                                    @endphp
                                                    <div class="leave-dot {{ $dotClass }}" title="{{ ucfirst($leave['type']) }} Leave ({{ ucfirst($leave['status']) }})">
                                                        {{ $leave['status'] === 'pending' ? 'P' : $leave['acronym'] }}
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

            <!-- Recent Leave Requests -->
            <div class="leave-requests-list">
                <h2>Recent Leave Requests</h2>
                @forelse($leaveRequests->sortByDesc('created_at')->take(3) as $request)
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
                            <span class="status-badge status-{{ str_replace('_', '-', $request->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                            <button type="button" class="btn btn-secondary btn-track" onclick="openTrackingModal({{ $request->id }})" title="Track Request"><i class="fas fa-question"></i></button>
                            @if($request->status === 'pending_admin')
                                <form action="{{ route('leave.cancel', $request->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-cancel" title="Cancel Request"><i class="fas fa-times"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Tracking Modal for this request -->
                    <div class="modal tracking-modal" id="trackingModal{{ $request->id }}">
                        <div class="modal-content tracking-modal-content">
                            <div class="modal-header">
                                <h2>Request Tracking</h2>
                                <button type="button" class="modal-close" onclick="closeTrackingModal({{ $request->id }})">&times;</button>
                            </div>
                            <div class="tracking-info">
                                <p><strong>{{ $request->leaveType->name }}</strong></p>
                                <p>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</p>
                                <p class="tracking-status status-{{ $request->status }}">
                                    Status: {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </p>
                            </div>

                            @php
                                $status = $request->status;
                                $user = auth()->user();
                                
                                // Manager workflow: Manager → Admin (simple 2-step)
                                $employeeStatus = 'completed'; // Manager submitted
                                $adminStatus = 'waiting';
                                
                                if ($status === 'pending_admin') {
                                    $adminStatus = 'current';
                                } elseif ($status === 'approved') {
                                    $adminStatus = 'completed';
                                } elseif ($status === 'rejected') {
                                    $adminStatus = 'rejected';
                                } elseif ($status === 'cancelled') {
                                    $adminStatus = 'cancelled';
                                }
                            @endphp

                            <div class="tracking-chain">
                                <!-- You (Manager) -->
                                <div class="tracking-step {{ $employeeStatus }}">
                                    <div class="step-icon">✓</div>
                                    <div class="step-info">
                                        <span class="step-role">You (Manager)</span>
                                        <span class="step-name">{{ $user->name }}</span>
                                        <span class="step-detail">Submitted {{ $request->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>

                                <div class="chain-connector {{ $adminStatus }}"></div>
                                <!-- Admin -->
                                <div class="tracking-step {{ $adminStatus }}">
                                    <div class="step-icon">
                                        @if($adminStatus === 'completed')
                                            ✓
                                        @elseif($adminStatus === 'rejected')
                                            ✗
                                        @elseif($adminStatus === 'cancelled')
                                            ○
                                        @elseif($adminStatus === 'current')
                                            ●
                                        @else
                                            2
                                        @endif
                                    </div>
                                    <div class="step-info">
                                        <span class="step-role">Admin</span>
                                        <span class="step-name">Final Approval</span>
                                        @if($adminStatus === 'completed')
                                            <span class="step-detail">Approved</span>
                                        @elseif($adminStatus === 'rejected')
                                            <span class="step-detail rejected-text">Rejected</span>
                                        @elseif($adminStatus === 'cancelled')
                                            <span class="step-detail cancelled-text">Cancelled at this stage</span>
                                        @elseif($adminStatus === 'current')
                                            <span class="step-detail pending-text">Awaiting approval</span>
                                        @else
                                            <span class="step-detail">Pending</span>
                                        @endif
                                        @if($request->admin_remarks)
                                            <span class="step-remarks">"{{ $request->admin_remarks }}"</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($status === 'cancelled')
                            <div class="cancelled-info">
                                <p><strong>Cancelled by:</strong> {{ $user->name }} (You)</p>
                            </div>
                            @endif

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="closeTrackingModal({{ $request->id }})">Close</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="no-requests">No leave requests yet</p>
                @endforelse
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
@php
$cancelledLeaveIds = $leaveRequests
    ->whereNotIn('status', ['approved','pending_admin','pending_manager'])
    ->pluck('id');
@endphp

<script>
// Tracking modal functions
function openTrackingModal(id) {
    document.getElementById('trackingModal' + id).classList.add('active');
}

function closeTrackingModal(id) {
    document.getElementById('trackingModal' + id).classList.remove('active');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('tracking-modal')) {
        e.target.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function () {

    const cancelledLeaveIds = @json($cancelledLeaveIds);

    cancelledLeaveIds.forEach(id => {
        const dot = document.querySelector(`.leave-dot[data-leave-id="${id}"]`);
        if (dot) dot.remove();
    });

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
            const dayOfWeek = currentDate.getDay(); // 0 = Sunday, 6 = Saturday
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

        const sessions = Array.from(sessionSelects).map(select => select.value);
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        // Count only weekdays between start and end
        const start = new Date(startDate + 'T00:00:00');
        const end = new Date(endDate + 'T00:00:00');
        let weekdayCount = 0;
        let d = new Date(start);
        while (d <= end) {
            const day = d.getDay();
            if (day !== 0 && day !== 6) weekdayCount++;
            d.setDate(d.getDate() + 1);
        }

        console.log('Form submission debug info:');
        console.log('  Start date:', startDate);
        console.log('  End date:', endDate);
        console.log('  Expected weekdays (JS):', weekdayCount);
        console.log('  Session selects found:', sessionSelects.length);
        console.log('  Sessions:', sessions);

        if (sessions.length !== weekdayCount) {
            console.error(`Mismatch: expected ${weekdayCount} sessions but found ${sessions.length}`);
        }
    });

});
</script>
</script>
</body>
</html>
