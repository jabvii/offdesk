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
                <li><a href="{{ route(name: 'admin.dashboard') }}">Dashboard</a></li>
                <li>
                    <a href="{{ route('admin.leave.requests') }}" class="active">
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.accounts') }}">
                        Accounts
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

    <!-- Main content -->
    <div class="main-content">
        <div class="container">

            <div class="dashboard-greeting">
                <span>Welcome, {{ auth()->user()->name }}!</span>
            </div>

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
                                @php
                                    if ($leave->start_session && $leave->start_session !== 'full') {
                                        echo "<br><small>Start: " . ucfirst($leave->start_session) . "</small>";
                                    }
                                    if ($leave->end_session && $leave->end_session !== 'full') {
                                        echo "<br><small>End: " . ucfirst($leave->end_session) . "</small>";
                                    }
                                @endphp
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

<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('adminActionModal');
    const modalTitle = document.getElementById('adminActionTitle');
    const form = document.getElementById('adminActionForm');
    const statusInput = document.getElementById('adminActionStatus');
    const remarks = document.getElementById('adminRemarks');

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

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') modal.style.display = 'none';
    });
});
</script>

</body>
</html>