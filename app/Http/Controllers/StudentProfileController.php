<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    public function edit(): View
    {
        abort_unless(Auth::user()->isStudent(), 403);

        return view('student.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->isStudent(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'in:Freshman,Sophomore,Junior,Senior,Graduate'],
            'monthly_allowance' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'savings_goal' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        Auth::user()->update($validated);

        return redirect()->route('profile.edit')->with('status', 'Your profile has been updated.');
    }
}
