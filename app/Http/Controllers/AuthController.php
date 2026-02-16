<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (!$user->is_admin) {

            if ($user->status === 'pending') {
                Auth::logout();
                return redirect()->route('login')
                    ->with('pending', 'Your account is awaiting admin approval.');
            }

            if ($user->status === 'rejected') {
                Auth::logout();
                return redirect()->route('login')
                    ->with('rejected', 'Your account was rejected by the administrator.');
            }
        }

        // Role-based redirects
        return match($user->role) {
            'technical' => redirect()->route('technical.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'department' => ['required', 'string', 'in:IT,Accounting,HR,Treasury,Sales,Planning,Visual,Engineering'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department' => $validated['department'],
            'is_admin' => false,
            'status' => 'pending',
            'role' => 'employee',
        ]);

        return redirect()->route('login')
            ->with('success', 'Your account has been created and is pending admin approval.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}