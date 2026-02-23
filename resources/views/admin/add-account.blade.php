<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Add Account</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/accounts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/add-account.css') }}">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Admin</h2>
            <ul class="nav-links">
                <li><a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) class="active" @endif>Dashboard</a></li>
                <li><a href="{{ route('admin.leave.requests') }}" @if(request()->routeIs('admin.leave.requests')) class="active" @endif>Requests</a></li>
                <li><a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>Accounts</a></li>
                <li><a href="{{ route('admin.add.account') }}" @if(request()->routeIs('admin.add.account')) class="active" @endif>Add Account</a></li>
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
            <!-- Validation Errors -->
            @if($errors->any())
                <div class="alert alert-error">
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="add-account-container">
                <div class="add-account-header">
                    <h2>Create New Account</h2>
                    <p>Add a new manager or employee account</p>
                </div>

                <form method="POST" action="{{ route('admin.store.account') }}" onsubmit="return confirmCreateAccount()">
                    @csrf

                    <!-- Name -->
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            required
                            autofocus
                            value="{{ old('name') }}"
                            placeholder="Enter full name"
                        >
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="Enter email address"
                        >
                    </div>

                    <!-- Password & Confirmation -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="Minimum 8 characters"
                                autocomplete="new-password"
                            >
                            <small>Minimum 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                placeholder="Confirm password"
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select name="department" id="department" required onchange="filterManagersByDepartment()">
                            <option value="">Select Department</option>
                            <option value="IT" @selected(old('department') === 'IT')>IT</option>
                            <option value="Accounting" @selected(old('department') === 'Accounting')>Accounting</option>
                            <option value="HR" @selected(old('department') === 'HR')>HR</option>
                            <option value="Treasury" @selected(old('department') === 'Treasury')>Treasury</option>
                            <option value="Sales" @selected(old('department') === 'Sales')>Sales</option>
                            <option value="Planning" @selected(old('department') === 'Planning')>Planning</option>
                            <option value="Visual" @selected(old('department') === 'Visual')>Visual</option>
                            <option value="Engineering" @selected(old('department') === 'Engineering')>Engineering</option>
                        </select>
                    </div>

                    <!-- Role -->
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" id="role" required onchange="toggleManagerField()">
                            <option value="">Select Role</option>
                            <option value="employee" @selected(old('role') === 'employee')>Employee</option>
                            <option value="supervisor" @selected(old('role') === 'supervisor')>Supervisor</option>
                            <option value="manager" @selected(old('role') === 'manager')>Manager</option>
                        </select>
                    </div>

                    <!-- Supervisor Selection (for employees and supervisors) -->
                    <div class="form-group" id="supervisorField" style="display: none;">
                        <label for="supervisor_id">Assign Supervisor (Optional)</label>
                        <select name="supervisor_id" id="supervisor_id">
                            <option value="">Auto-assign from department</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}" data-department="{{ $supervisor->department }}" @selected(old('supervisor_id') == $supervisor->id)>
                                    {{ $supervisor->name }} ({{ $supervisor->department }})
                                </option>
                            @endforeach
                        </select>
                        <small>Supervisors who report to a manager</small>
                    </div>

                    <!-- Manager Selection (for employees and supervisors) -->
                    <div class="form-group" id="managerField" style="display: none;">
                        <label for="manager_id">Assign Manager (Optional)</label>
                        <select name="manager_id" id="manager_id">
                            <option value="">Auto-assign from department</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" data-department="{{ $manager->department }}" @selected(old('manager_id') == $manager->id)>
                                    {{ $manager->name }} ({{ $manager->department }})
                                </option>
                            @endforeach
                        </select>
                        <small>Managers who oversee the department</small>
                    </div>

                    <!-- Is Supervisor Checkbox (for manager role) -->
                    <div class="form-group" id="isSupervisorField" style="display: none;">
                        <label for="is_supervisor">
                            <input type="checkbox" name="is_supervisor" id="is_supervisor" value="1" @checked(old('is_supervisor'))>
                            <span>This person can also supervise employees in their department</span>
                        </label>
                    </div>

                    <div class="manager-note" id="managerNote" style="display: none;">
                        <strong>Note:</strong> Managers oversee the department. Supervisors report to a manager and can supervise employees.
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

function confirmCreateAccount() {
    const name = document.getElementById('name').value;
    const role = document.getElementById('role').value;
    const department = document.getElementById('department').value;
    
    if (!name || !role || !department) {
        alert('Please fill in all required fields.');
        return false;
    }
    
    const message = `Are you sure you want to create a ${role} account for ${name} in ${department}?`;
    return confirm(message);
}

function toggleManagerField() {
    const roleSelect = document.getElementById('role');
    const managerField = document.getElementById('managerField');
    const supervisorField = document.getElementById('supervisorField');
    const isSupervisorField = document.getElementById('isSupervisorField');
    const managerNote = document.getElementById('managerNote');
    const role = roleSelect.value;

    // Reset all visibility
    managerField.style.display = 'none';
    supervisorField.style.display = 'none';
    isSupervisorField.style.display = 'none';
    managerNote.style.display = 'none';

    // Show/hide based on role
    if (role === 'employee') {
        // Employee: needs both supervisor and manager
        supervisorField.style.display = 'block';
        managerField.style.display = 'block';
    } else if (role === 'supervisor') {
        // Supervisor: only needs manager (not another supervisor)
        managerField.style.display = 'block';
        managerNote.style.display = 'block';
    } else if (role === 'manager') {
        // Manager: can be marked as supervisor
        isSupervisorField.style.display = 'block';
        managerNote.style.display = 'block';
    }
}

function filterManagersByDepartment() {
    const departmentSelect = document.getElementById('department');
    const managerSelect = document.getElementById('manager_id');
    const supervisorSelect = document.getElementById('supervisor_id');
    const selectedDepartment = departmentSelect.value;

    // Filter managers
    const allManagerOptions = managerSelect.querySelectorAll('option');
    managerSelect.value = '';
    allManagerOptions.forEach((option, index) => {
        if (index === 0) return;
        const managerDepartment = option.getAttribute('data-department');
        option.style.display = managerDepartment === selectedDepartment ? '' : 'none';
    });

    // Filter supervisors
    const allSupervisorOptions = supervisorSelect.querySelectorAll('option');
    supervisorSelect.value = '';
    allSupervisorOptions.forEach((option, index) => {
        if (index === 0) return;
        const supervisorDepartment = option.getAttribute('data-department');
        option.style.display = supervisorDepartment === selectedDepartment ? '' : 'none';
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleManagerField();
    // Filter managers if department was already selected (e.g., on validation error)
    const department = document.getElementById('department').value;
    if (department) {
        filterManagersByDepartment();
    }
});
</script>
</body>
</html>
