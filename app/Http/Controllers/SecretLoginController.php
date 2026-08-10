<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SecretLoginController extends Controller
{
    public function create()
    {
        return view('auth/secret_login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = $request->input('username');
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            $isAdminOrRoot = $user->hasRole('admin') || $user->hasRole('root');
            $hasAssignedTabs = $user->getSidebarTabs()->isNotEmpty();

            if (!$isAdminOrRoot && !$hasAssignedTabs) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'username' => 'Access denied. You do not have permission to access the management dashboard.',
                ]);
            }
            $request->session()->regenerate();

            if ($user->is_first_login) {
                return redirect()->route('password.change.form');
            }

            return redirect()->intended('/topsecret/dashboard');
        }

        throw ValidationException::withMessages([
            'username' => trans('auth.failed'),
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
