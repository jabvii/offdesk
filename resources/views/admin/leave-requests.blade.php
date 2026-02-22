<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Admin Requests</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/leave-requests.css') }}">
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
                <li>
                    <a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>
                        Accounts
                        @if($pendingUsersCount > 0)
                            <span class="badge">{{ $pendingUsersCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('admin.add.account') }}" @if(request()->routeIs('admin.add.account')) class="active" @endif>Add Account</a></li>
                <li><a href="{{ route('admin.approved_accounts') }}" @if(request()->routeIs('admin.approved_accounts')) class="active" @endif>Approved Users</a></li>
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

            <h2 class="dblue">Pending Leave Requests (Awaiting Admin Approval)</h2>

            <div class="requests-table-wrapper">
                <table border="1" cellpadding="8" cellspacing="0" class="requests-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Request Type</th>
                            <th>Manager/Notes</th>
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
                            <td>
                                @if($leave->user->isManager())
                                    <span style="background: #B8956A; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Manager Request</span>
                                @else
                                    <span style="background: #5B8DBE; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Employee Request</span>
                                @endif
                            </td>
                            <td> 
                                @if($leave->manager)
                                    <strong>{{ $leave->manager->name }}</strong><br>
                                    <small style="color: #888;">{{ $leave->manager_remarks ?? 'No remarks' }}</small>
                                @else
                                    <small style="color: #888;">Direct to admin</small>
                                @endif
                            </td>
                            <td>
                                <button class="admin-action-btn" data-id="{{ $leave->id }}" data-action="approved">
                                    Approve
                                </button>
                                <button class="admin-action-btn" data-id="{{ $leave->id }}" data-action="rejected">
                                    Reject
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">No pending leave requests 🎉</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Admin Action Modal -->
<div id="adminActionModal" class="modal">
    <div class="modal-content">
        <h3 id="adminActionTitle"></h3>

        <form id="adminActionForm" method="POST">
            @csrf
            <input type="hidden" name="status" id="adminActionStatus">

            <textarea
                name="admin_remarks"
                id="adminRemarks"
                rows="4"
                placeholder="Enter reason or remarks..."
                required>
            </textarea>

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
            <button type="button" class="close-sessions-btn" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
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
</div>

<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('adminActionModal');
    const viewSessionsModal = document.getElementById('viewSessionsModal');
    const modalTitle = document.getElementById('adminActionTitle');
    const form = document.getElementById('adminActionForm');
    const statusInput = document.getElementById('adminActionStatus');
    const remarks = document.getElementById('adminRemarks');
    const sessionsTableBody = document.getElementById('sessionsTableBody');

    // Handle Approve/Reject buttons
    document.querySelectorAll('.admin-action-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action; // approved / rejected
            const leaveId = this.dataset.id;

            modalTitle.textContent = action === 'approved' ? 'Approve Leave Request' : 'Reject Leave Request';
            statusInput.value = action;

            // form URL matches route in web.php
            form.action = `/admin/employee/leave-requests/${leaveId}/decision`;

            remarks.value = '';
            modal.style.display = 'flex';
        });
    });

    document.querySelector('.conf-btn').addEventListener('click', function() {
        if(remarks.value.trim() === '') {
            alert('Please enter remarks before confirming.');
            return;
        }
        form.submit();
    });

    document.querySelector('.canc-btn').addEventListener('click', function() {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', function(e) {
        if(e.target === modal) modal.style.display = 'none';
    });

    // Handle View Sessions button
    document.querySelectorAll('.view-sessions-btn').forEach(button => {
        button.addEventListener('click', function() {
            const leaveId = this.dataset.leaveId;

            // Fetch sessions from server
            fetch(`/admin/employee/leave-requests/${leaveId}/sessions`)
                .then(response => response.json())
                .then(data => {
                    sessionsTableBody.innerHTML = '';

                    const startDate = new Date(data.start_date + 'T00:00:00');
                    const endDate = new Date(data.end_date + 'T00:00:00');

                    // Create a map of sessions by date for quick lookup
                    const sessionMap = {};
                    data.sessions.forEach(session => {
                        sessionMap[session.date] = session.session;
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
                            row.innerHTML = `
                                <td>${formattedDate} <span style="font-weight: normal; color: #666;">(${dayName})</span></td>
                                <td>${sessionDisplay}</td>
                            `;
                        }

                        sessionsTableBody.appendChild(row);
                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    viewSessionsModal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching sessions:', error);
                    alert('Error loading sessions');
                });
        });
    });

    document.querySelector('.close-sessions-btn').addEventListener('click', function() {
        viewSessionsModal.style.display = 'none';
    });

    viewSessionsModal.addEventListener('click', function(e) {
        if(e.target === viewSessionsModal) viewSessionsModal.style.display = 'none';
    });

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') {
            modal.style.display = 'none';
            viewSessionsModal.style.display = 'none';
        }
    });
});
</script>

</body>
</html>