<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDESK GUESS</h2>
            <ul class="nav-links">
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
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
                    if(!in_array($status, ['approved','pending_manager', 'pending_admin'])) continue;

                    $start = \Carbon\Carbon::parse($request->start_date);
                    $end = \Carbon\Carbon::parse($request->end_date);

                    while ($start->lte($end)) {
                        // Determine session for each day
                        if ($start->eq(\Carbon\Carbon::parse($request->start_date)) && $start->eq(\Carbon\Carbon::parse($request->end_date))) {
                            // Same day - use both start and end session, but primary is start_session
                            $session = $request->start_session ?? 'full';
                        } elseif ($start->eq(\Carbon\Carbon::parse($request->start_date))) {
                            // First day of multi-day leave
                            $session = $request->start_session ?? 'full';
                        } elseif ($start->eq(\Carbon\Carbon::parse($request->end_date))) {
                            // Last day of multi-day leave
                            $session = $request->end_session ?? 'full';
                        } else {
                            // Middle days - always full
                            $session = 'full';
                        }

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
                                        @php $dayLeaves = $leaveDays[$month][$day] ?? []; @endphp
                                        <td class="calendar-day {{ count($dayLeaves) ? 'has-leave' : '' }}">
                                            @foreach($dayLeaves as $leave)
                                                <div class="leave-dot {{ $leave['type'] }} {{ $leave['session'] }}">
                                                    {{-- Optionally add a label inside the dot --}}
                                                    {{ $leave['session'] !== 'full' ? strtoupper($leave['session'][0]) : '' }}
                                                </div>
                                            @endforeach
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
                                ({{ $request->total_days }} day{{ $request->total_days > 1 ? 's' : '' }}
                                @php
                                    $showSession = false;
                                    if ($request->start_session && $request->start_session !== 'full') {
                                        echo " - Start: " . ucfirst($request->start_session);
                                        $showSession = true;
                                    }
                                    if ($request->end_session && $request->end_session !== 'full' && $request->end_session !== $request->start_session) {
                                        echo " - End: " . ucfirst($request->end_session);
                                        $showSession = true;
                                    }
                                @endphp)
                            </p>

                            <p>{{ $request->reason }}</p>

                            @if($request->status !== 'pending' && $request->admin_remarks)
                                <p class="pdark"><strong>Admin Remarks:</strong> {{ $request->admin_remarks }}</p>
                            @endif
                        </div>

                        <div class="leave-request-actions">
                            <span class="status-badge status-{{ $request->status }}">
                                @if($request->status === 'pending_manager')
                                    Pending Manager Review
                                @elseif($request->status === 'pending_admin')
                                    Pending Admin Approval
                                @elseif($request->status === 'approved')
                                    Approved
                                @elseif($request->status === 'rejected')
                                    Rejected
                                @else
                                    {{ ucfirst($request->status) }}
                                @endif
                            </span>
                            @if(in_array($request->status, ['pending_manager', 'pending_admin']))
                                <form action="{{ route('leave.cancel', $request->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            @endif
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
        <form action="{{ route('leave.store') }}" method="POST">
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
            <div class="form-group">
                <label for="start_session">Start Date Session</label>
                <select name="start_session" id="start_session">
                    <option value="full">Full Day</option>
                    <option value="morning">Morning (8:00am-12:00pm)</option>
                    <option value="afternoon">Afternoon (1:00pm-5:30pm)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="end_session">End Date Session</label>
                <select name="end_session" id="end_session">
                    <option value="full">Full Day</option>
                    <option value="morning">Morning (8:00am-12:00pm)</option>
                    <option value="afternoon">Afternoon (1:00pm-5:30pm)</option>
                </select>
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
// Remove dots for cancelled requests on page load
const cancelledLeaveIds = @json($leaveRequests->whereNotIn('status', ['approved','pending'])->pluck('id'));
document.addEventListener('DOMContentLoaded', function() {
    cancelledLeaveIds.forEach(id => {
        const dot = document.querySelector(`.leave-dot[data-leave-id="${id}"]`);
        if(dot) dot.remove();
    });
});
</script>
</body>
</html>