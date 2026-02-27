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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Supervisor</h2>
            <ul class="nav-links">
                <li><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
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
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> 
                        @forelse($pendingRequests as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}<br><small style="color: #888;">{{ $leave->user->department }}</small></td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} → {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td>
                                <button type="button" class="btn btn-view-details" onclick="openDetailsModal({{ $leave->id }})" title="View Details"><i class="fas fa-eye"></i></button>
                            </td>
                            <td>
                                <button class="action-btn action-approve" data-id="{{ $leave->id }}" data-action="approved" title="Approve"><i class="fas fa-check"></i></button>
                                <button class="action-btn action-reject" data-id="{{ $leave->id }}" data-action="rejected" title="Reject"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>

                        <!-- Details Modal for this request -->
                        <div class="modal details-modal" id="detailsModal{{ $leave->id }}">
                            <div class="modal-content details-modal-content">
                                <div class="modal-header">
                                    <h2>Leave Request Details</h2>
                                    <button type="button" class="modal-close" onclick="closeDetailsModal({{ $leave->id }})">&times;</button>
                                </div>
                                
                                <div class="details-section">
                                    <h3>Employee Information</h3>
                                    <div class="details-grid">
                                        <div class="detail-item">
                                            <span class="detail-label">Name</span>
                                            <span class="detail-value">{{ $leave->user->name }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Department</span>
                                            <span class="detail-value">{{ $leave->user->department ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Email</span>
                                            <span class="detail-value">{{ $leave->user->email }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Leave Details</h3>
                                    <div class="details-grid">
                                        <div class="detail-item">
                                            <span class="detail-label">Leave Type</span>
                                            <span class="detail-value leave-type-badge {{ strtolower(str_replace(' ', '-', $leave->leaveType->name)) }}">{{ $leave->leaveType->name }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Duration</span>
                                            <span class="detail-value">{{ $leave->total_days }} day(s)</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Start Date</span>
                                            <span class="detail-value">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y (l)') }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">End Date</span>
                                            <span class="detail-value">{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y (l)') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Daily Sessions</h3>
                                    <div class="sessions-list">
                                        @php
                                            $sessions = $leave->sessions ?? collect();
                                            $sessionMap = $sessions->keyBy(fn($s) => $s->date->format('Y-m-d'));
                                            $start = \Carbon\Carbon::parse($leave->start_date);
                                            $end = \Carbon\Carbon::parse($leave->end_date);
                                        @endphp
                                        @for($date = $start->copy(); $date->lte($end); $date->addDay())
                                            @php
                                                $dateStr = $date->format('Y-m-d');
                                                $isWeekend = $date->isWeekend();
                                                $session = $sessionMap->get($dateStr);
                                                $sessionType = $session ? $session->session : 'whole_day';
                                            @endphp
                                            <div class="session-item {{ $isWeekend ? 'weekend' : '' }}">
                                                <span class="session-date">{{ $date->format('M d, Y') }} ({{ $date->format('l') }})</span>
                                                <span class="session-type">
                                                    @if($isWeekend)
                                                        <em>Weekend</em>
                                                    @else
                                                        {{ $sessionType === 'whole_day' ? 'Whole Day' : ucfirst($sessionType) }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Reason</h3>
                                    <div class="reason-box">
                                        {{ $leave->reason }}
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Submission Info</h3>
                                    <p class="submission-info">Submitted on {{ $leave->created_at->format('F d, Y \a\t h:i A') }}</p>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="closeDetailsModal({{ $leave->id }})">Close</button>
                                </div>
                            </div>
                        </div>
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
            <select name="supervisor_remarks" id="remarks" class="remarks-select" required>
                <option value="">-- Select Remarks --</option>
                <optgroup label="Approval" id="approvalOptions">
                    <option value="Approved as requested.">Approved as requested.</option>
                    <option value="Leave approved. Enjoy your time off.">Leave approved. Enjoy your time off.</option>
                    <option value="Approved. Please ensure proper handover.">Approved. Please ensure proper handover.</option>
                </optgroup>
                <optgroup label="Rejection" id="rejectionOptions">
                    <option value="Insufficient leave balance.">Insufficient leave balance.</option>
                    <option value="Request conflicts with team schedule.">Request conflicts with team schedule.</option>
                    <option value="Peak period - leave not permitted.">Peak period - leave not permitted.</option>
                    <option value="Please resubmit with correct dates.">Please resubmit with correct dates.</option>
                </optgroup>
            </select>
            <div class="modal-actions">
                <button type="button" class="conf-btn">Confirm</button>
                <button type="button" class="canc-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
<script>
// Details Modal functions
function openDetailsModal(id) {
    document.getElementById('detailsModal' + id).classList.add('active');
}

function closeDetailsModal(id) {
    document.getElementById('detailsModal' + id).classList.remove('active');
}

// Close details modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('details-modal')) {
        e.target.classList.remove('active');
    }
});

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
const modalTitle = document.getElementById('actionTitle');
const form = document.getElementById('actionForm');
const statusInput = document.getElementById('actionStatus');
const remarks = document.getElementById('remarks');
const approvalOptions = document.getElementById('approvalOptions');
const rejectionOptions = document.getElementById('rejectionOptions');

document.querySelectorAll('.action-btn').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.dataset.action;
        const leaveId = this.dataset.id;
        modalTitle.textContent = action === 'approved' ? 'Approve Request' : 'Reject Request';
        statusInput.value = action;
        form.action = `/supervisor/leave-requests/${leaveId}/decision`;
        remarks.value = '';
        
        // Show/hide relevant options based on action
        if (action === 'approved') {
            approvalOptions.style.display = '';
            rejectionOptions.style.display = 'none';
        } else {
            approvalOptions.style.display = 'none';
            rejectionOptions.style.display = '';
        }
        
        actionModal.style.display = 'flex';
    });
});
document.querySelector('.conf-btn').addEventListener('click', function() {
    if(remarks.value.trim() === '') { alert('Please select remarks'); return; }
    form.submit();
});
document.querySelectorAll('.canc-btn').forEach(btn => btn.addEventListener('click', ()=> actionModal.style.display='none'));
actionModal.addEventListener('click', e => { if(e.target === actionModal) actionModal.style.display='none'; });

// Close all modals on Escape
document.addEventListener('keydown', e=>{
    if(e.key==='Escape'){
        leaveRequestModal.style.display='none';
        actionModal.style.display='none';
        document.querySelectorAll('.details-modal.active').forEach(modal => modal.classList.remove('active'));
    }
});
</script>
</body>
</html>