<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    public function showForm()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'new_password' => ['required', 'min:8', 'confirmed', Password::defaults()],
        ], [
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->new_password);
        $user->is_first_login = false;
        $user->save();

        if ($user->hasRole('root') || $user->hasRole('admin')) {
            return redirect('/topsecret/dashboard')->with('success', 'Đổi mật khẩu thành công!');
        }

        return redirect('/')->with('success', 'Đổi mật khẩu thành công!');
    }
}
