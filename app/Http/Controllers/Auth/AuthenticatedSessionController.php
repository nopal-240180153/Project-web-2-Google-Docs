<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Jika login dari halaman lain, kembalikan ke tujuan itu
        $redirectTo = $request->query('redirect');

        if ($redirectTo) {
            // Pastikan redirectTo tidak mengarah ke path kosong/invalid.
            // Jika value berupa relative path, redirect()->to() tetap aman.
            $redirectTo = is_string($redirectTo) ? trim($redirectTo) : null;
            if ($redirectTo) {
                return redirect()->to($redirectTo);
            }
        }


        // Default: diarahkan ke dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Setelah logout diarahkan kembali ke halaman welcome depan
        return redirect('/');
    }
}