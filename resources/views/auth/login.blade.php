<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - OffDesk</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

{{-- Success Modal --}}
@if(session('success'))
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Account Created</h3>
        <p>{{ session('success') }}</p>
        <button class="modal-close">Okay</button>
    </div>
</div>
@endif

{{-- Pending Modal --}}
@if(session('pending'))
<div id="pendingModal" class="modal">
    <div class="modal-content">
        <h3>Account Pending</h3>
        <p>Your account is pending admin approval.</p>
        <button class="modal-close">Close</button>
    </div>
</div>
@endif

{{-- Rejected Modal --}}
@if(session('rejected'))
<div id="rejectedModal" class="modal">
    <div class="modal-content">
        <h3>Account Rejected</h3>
        <p>{{ session('rejected') }}</p>
        <button class="modal-close">Close</button>
    </div>
</div>
@endif

<div class="auth-container">
    <div class="auth-box">
        <h1>OffDesk</h1>
        <p class="subtitle">Sign in to your account</p>

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" name="remember">
                <label>Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <div class="auth-link">
            Don't have an account?
            <a href="{{ route('register') }}">Sign up</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modals = document.querySelectorAll('.modal');

    modals.forEach(modal => {
        modal.style.display = 'flex';

        modal.querySelector('.modal-close').onclick = () => {
            modal.style.display = 'none';
        };

        modal.onclick = e => {
            if (e.target === modal) modal.style.display = 'none';
        };            
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') modal.style.display = 'none';
        });
    });
});
</script>

</body>
</html>