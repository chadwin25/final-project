<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.home');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email:rfc', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('web')->attempt($credentials + ['role' => ['admin', 'staff']])) {
            Auth::guard('customer')->logout();
            $request->session()->forget(['customer_cart', 'url.intended']);
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        if (Auth::guard('customer')->attempt($credentials)) {
            Auth::guard('web')->logout();
            $request->session()->forget(['customer_cart', 'url.intended']);
            $request->session()->regenerate();

            return redirect()->route('customer.home');
        }

        return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
