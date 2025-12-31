<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
     */
    public function store(Request $request)
    {
        // ✅ Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // ✅ Create user (NO auto-login)
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ Redirect to login page with success message
        return redirect('/login')
            ->with('success', 'Registration successful. Please login to continue.');
    }
}
