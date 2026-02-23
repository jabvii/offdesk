<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - OffDesk</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
</head>
<body>

<!-- Success Modal (after registration) -->
@if(session('success'))
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Account Created ✅</h3>
        <p>{{ session('success') }}</p>
        <button class="modal-close">Okay</button>
    </div>
</div>
@endif

<div class="auth-container">
    <div class="auth-box">
        <h1>Create Account</h1>
        <p class="subtitle">Sign up to get started</p>
        
        <!-- Validation Errors -->
        @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        required
                        value="{{ old('name') }}"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        value="{{ old('email') }}"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
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
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department</label>
                    <select
                        name="department"
                        id="department"
                        required
                    >
                        <option value="">Select a Department</option>
                        <option value="IT">IT</option>
                        <option value="Accounting">Accounting</option>
                        <option value="HR">HR</option>
                        <option value="Treasury">Treasury</option>
                        <option value="Sales">Sales</option>
                        <option value="Planning">Planning</option>
                        <option value="Visual">Visual</option>
                        <option value="Engineering">Engineering</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-link">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('successModal');
    if(modal) {
        modal.style.display = 'flex';

        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => modal.style.display = 'none');

        modal.addEventListener('click', e => {
            if(e.target === modal) modal.style.display = 'none';
        });

        document.addEventListener('keydown', e => {
            if(e.key === 'Escape') modal.style.display = 'none';
        });
    }
});
</script>

</body>
</html>