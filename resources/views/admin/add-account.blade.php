<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Add Account</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .add-account-container {
            background: #1f2937;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto;
        }

        .add-account-header {
            margin-bottom: 30px;
        }

        .add-account-header h2 {
            color: white;
            margin: 0 0 10px 0;
        }

        .add-account-header p {
            color: #9ca3af;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #4b5563;
            border-radius: 6px;
            background: #374151;
            color: white;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .form-group select option {
            background: #1f2937;
            color: white;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            background: #2d3748;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #9ca3af;
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .error-list li {
            padding: 4px 0;
        }

        .manager-note {
            background: #374151;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #d1d5db;
            font-size: 13px;
            display: none;
        }

        .manager-note.show {
            display: block;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk GUESS</h2>
            <ul class="nav-links">
                <li><a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) class="active" @endif>Dashboard</a></li>
                <li><a href="{{ route('admin.leave.requests') }}" @if(request()->routeIs('admin.leave.requests')) class="active" @endif>Requests</a></li>
                <li><a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>Accounts</a></li>
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
                            <option value="manager" @selected(old('role') === 'manager')>Manager</option>
                        </select>
                    </div>

                    <!-- Manager Selection (for employees only) -->
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
                        <small>Leave empty to auto-assign manager from department</small>
                    </div>

                    <div class="manager-note" id="managerNote">
                        <strong>Note:</strong> Managers have no manager assigned. They can be assigned as a supervisor for employees.
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="{{ route('admin.accounts') }}" class="btn btn-secondary">Cancel</a>
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
    const managerNote = document.getElementById('managerNote');

    if (roleSelect.value === 'employee') {
        managerField.style.display = 'block';
        managerNote.classList.remove('show');
    } else if (roleSelect.value === 'manager') {
        managerField.style.display = 'none';
        managerNote.classList.add('show');
    } else {
        managerField.style.display = 'none';
        managerNote.classList.remove('show');
    }
}

function filterManagersByDepartment() {
    const departmentSelect = document.getElementById('department');
    const managerSelect = document.getElementById('manager_id');
    const selectedDepartment = departmentSelect.value;

    const allOptions = managerSelect.querySelectorAll('option');

    // Reset to auto-assign option
    managerSelect.value = '';

    let visibleCount = 1; // Start at 1 for the "Auto-assign" option

    allOptions.forEach((option, index) => {
        if (index === 0) return; // Skip the "Auto-assign" option

        const managerDepartment = option.getAttribute('data-department');

        // Show managers matching the selected department
        if (managerDepartment === selectedDepartment) {
            option.style.display = '';
            visibleCount++;
        } else {
            option.style.display = 'none';
        }
    });

    // If only auto-assign option is visible, show a message
    if (visibleCount === 1 && selectedDepartment) {
        // No matching managers found, will use auto-assign
    }
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
