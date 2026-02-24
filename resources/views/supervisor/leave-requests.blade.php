<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Supervisor Leave Requests</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supervisor/leave-requests.css') }}">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Supervisor</h2>
            <ul class="nav-links">
                <li><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
                <li> 
                    <a href="{{ route('supervisor.leave.requests') }}" class="active">
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

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <h2 class="dblue">Pending Leave Requests (Awaiting Supervisor Approval)</h2>

            <div class="requests-table-wrapper">
                <table border="1" cellpadding="8" cellspacing="0" class="requests-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> 
                        @forelse($pendingRequests as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}<br><small style="color: #888;">{{ $leave->user->department }}</small></td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} → {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                <br>
                                <button type="button" class="view-sessions-btn" data-leave-id="{{ $leave->id }}" style="margin-top: 5px; padding: 3px 6px; font-size: 12px;">View Sessions</button>
                            </td>
                            <td>{{ $leave->total_days }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>
                                <button class="action-btn" data-id="{{ $leave->id }}" data-action="approved">Approve</button>
                                <button class="action-btn" data-id="{{ $leave->id }}" data-action="rejected">Reject</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">No pending requests 🎉</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Request Leave Modal (from dashboard) -->
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
            
            <div id="sessionsTableContainer" style="display: none; margin-top: 20px;">
                <label>Select Session for Each Day</label>
                <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Session</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsTableBody"></tbody>
                </table>
            </div>

            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" rows="4" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <button type="button" class="btn btn-secondary" onclick="closeLeaveRequestModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Action Modal (Approve/Reject) -->
<div id="actionModal" class="modal">
    <div class="modal-content">
        <h3 id="actionTitle"></h3>
        <form id="actionForm" method="POST">
            @csrf
            <input type="hidden" name="status" id="actionStatus">
            <textarea name="supervisor_remarks" id="remarks" rows="4" placeholder="Enter reason or remarks..." required></textarea>
            <div class="modal-actions">
                <button type="button" class="conf-btn">Confirm</button>
                <button type="button" class="canc-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View Sessions Modal -->
<div id="viewSessionsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Daily Sessions</h3>
            <button type="button" class="close-sessions-btn">&times;</button>
        </div>
        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 10px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Session</th>
                </tr>
            </thead>
            <tbody id="sessionsTableBodyModal"></tbody>
        </table>
    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

// Request Leave Modal
const leaveRequestModal = document.getElementById('leaveRequestModal');
function closeLeaveRequestModal() { leaveRequestModal.style.display = 'none'; }
document.getElementById('openLeaveModalLink').addEventListener('click', function(e){
    e.preventDefault();
    leaveRequestModal.style.display = 'flex';
});

// Generate daily sessions for leave request
const startDateInput = document.getElementById('start_date');
const endDateInput = document.getElementById('end_date');
const sessionsTableContainer = document.getElementById('sessionsTableContainer');
const sessionsTableBody = document.getElementById('sessionsTableBody');

function generateSessionsTable() {
    const start = new Date(startDateInput.value);
    const end = new Date(endDateInput.value);
    if (!startDateInput.value || !endDateInput.value || start > end) {
        sessionsTableContainer.style.display = 'none';
        return;
    }
    sessionsTableBody.innerHTML = '';
    let currentDate = new Date(start);
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    while(currentDate <= end){
        const formattedDate = currentDate.toLocaleDateString('en-US', { year:'numeric', month:'2-digit', day:'2-digit' });
        const dayOfWeek = currentDate.getDay();
        const dayName = dayNames[dayOfWeek];
        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const row = document.createElement('tr');

        if(isWeekend) {
            row.style.backgroundColor = '#f0f0f0';
            row.style.color = '#999';
            row.innerHTML = `
                <td>${formattedDate} <span style="font-weight: normal; color: #999;">(${dayName})</span></td>
                <td>
                    <span style="font-style: italic; color: #aaa;">
                        Weekend
                    </span>
                </td>
            `;
        } else {
            row.innerHTML = `<td>${formattedDate} <span style="font-weight: normal; color: #666;">(${dayName})</span></td>
                <td>
                    <select name="daily_sessions[]" class="session-select" required>
                        <option value="whole_day">Whole Day</option>
                        <option value="morning">Morning (8:00am-12:00pm)</option>
                        <option value="afternoon">Afternoon (1:00pm-5:30pm)</option>
                    </select>
                </td>`;
        }

        sessionsTableBody.appendChild(row);
        currentDate.setDate(currentDate.getDate() + 1);
    }
    sessionsTableContainer.style.display = 'block';
}
startDateInput.addEventListener('change', generateSessionsTable);
endDateInput.addEventListener('change', generateSessionsTable);

// Approve/Reject Modal
const actionModal = document.getElementById('actionModal');
const viewSessionsModal = document.getElementById('viewSessionsModal');
const modalTitle = document.getElementById('actionTitle');
const form = document.getElementById('actionForm');
const statusInput = document.getElementById('actionStatus');
const remarks = document.getElementById('remarks');

document.querySelectorAll('.action-btn').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.dataset.action;
        const leaveId = this.dataset.id;
        modalTitle.textContent = action === 'approved' ? 'Approve Request' : 'Reject Request';
        statusInput.value = action;
        form.action = `/supervisor/leave-requests/${leaveId}/decision`;
        remarks.value = '';
        actionModal.style.display = 'flex';
    });
});
document.querySelector('.conf-btn').addEventListener('click', function() {
    if(remarks.value.trim() === '') { alert('Enter remarks'); return; }
    form.submit();
});
document.querySelectorAll('.canc-btn').forEach(btn => btn.addEventListener('click', ()=> actionModal.style.display='none'));
actionModal.addEventListener('click', e => { if(e.target === actionModal) actionModal.style.display='none'; });

// View Sessions Modal
document.querySelector('.close-sessions-btn').addEventListener('click', ()=> viewSessionsModal.style.display='none');
document.querySelectorAll('.view-sessions-btn').forEach(button => {
    button.addEventListener('click', function() {
        const leaveId = this.dataset.leaveId;
        fetch(`/supervisor/leave-requests/${leaveId}/sessions`)
            .then(res => res.json())
            .then(data => {
                const body = document.getElementById('sessionsTableBodyModal');
                body.innerHTML = '';

                const startDate = new Date(data.start_date + 'T00:00:00');
                const endDate = new Date(data.end_date + 'T00:00:00');

                // Create a map of sessions by date for quick lookup
                const sessionMap = {};
                data.sessions.forEach(s => {
                    sessionMap[s.date] = s.session;
                });

                const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                // Generate all dates in range
                let currentDate = new Date(startDate);
                while(currentDate <= endDate) {
                    // Construct date string in local timezone to match database
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const dateStr = `${year}-${month}-${day}`;

                    const formattedDate = currentDate.toLocaleDateString('en-US', {year:'numeric', month:'2-digit', day:'2-digit'});
                    const dayOfWeek = currentDate.getDay();
                    const dayName = dayNames[dayOfWeek];
                    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

                    const row = document.createElement('tr');

                    if(isWeekend) {
                        row.style.backgroundColor = '#f0f0f0';
                        row.style.color = '#999';
                        row.innerHTML = `
                            <td>${formattedDate} <span style="font-weight: normal; color: #999;">(${dayName})</span></td>
                            <td><span style="font-style: italic; color: #aaa;">Weekend</span></td>
                        `;
                    } else {
                        const session = sessionMap[dateStr] || 'whole_day';
                        const sessionDisplay = session === 'whole_day' ? 'Whole Day' : session.charAt(0).toUpperCase() + session.slice(1);
                        row.innerHTML = `<td>${formattedDate} <span style="font-weight: normal; color: #666;">(${dayName})</span></td><td>${sessionDisplay}</td>`;
                    }

                    body.appendChild(row);
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                viewSessionsModal.style.display='flex';
            })
            .catch(()=> alert('Error loading sessions'));
    });
});

// Close all modals on Escape
document.addEventListener('keydown', e=>{
    if(e.key==='Escape'){
        leaveRequestModal.style.display='none';
        actionModal.style.display='none';
        viewSessionsModal.style.display='none';
    }
});
</script>
</body>
</html>
