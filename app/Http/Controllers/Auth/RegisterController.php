<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'academic_year' => ['required', 'in:Freshman,Sophomore,Junior,Senior,Graduate'],
            'monthly_allowance' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'savings_goal' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'status' => 'active',
            'academic_year' => $validated['academic_year'],
            'monthly_allowance' => $validated['monthly_allowance'],
            'savings_goal' => $validated['savings_goal'],
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Welcome to Campus Coin! Your student profile is initialized.');
    }
}
