<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Show the Login View
    public function showLogin()
    {
        return view('auth.login');
    }

    // 2. Handle the Login Request (called via Axios)
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Attempt authentication
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate(); // Security: prevent session fixation

            return response()->json([
                'message' => 'Login successful',
                'redirect' => route('dashboard') // We will define this route next
            ]);
        }

        // Authentication failed
        return response()->json([
            'errors' => [
                'email' => ['The provided credentials do not match our records.']
            ]
        ], 422); // 422 Unprocessable Entity
    }

    // 3. Handle Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}