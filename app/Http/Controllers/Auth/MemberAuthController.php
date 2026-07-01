<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MemberAuthController extends Controller
{
    public function createLogin(Request $request): View
    {
        return view('auth.member-login', [
            'station' => $request->string('station')->toString(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'station' => ['nullable', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['login'])
            ->orWhere('member_code', $credentials['login'])
            ->orWhere('phone', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'ID member, email, atau password tidak valid.',
            ])->onlyInput('login');
        }

        Auth::login($user, true);
        $user->forceFill(['last_seen_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()->route('portal.index', [
            'station' => $credentials['station'] ?: null,
        ]);
    }

    public function createRegister(Request $request): View
    {
        return view('auth.member-register', [
            'station' => $request->string('station')->toString(),
        ]);
    }

    public function register(Request $request, BillingService $billingService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'station' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
        ]);

        $billingService->ensureMemberRole($user);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('portal.index', [
            'station' => $data['station'] ?: null,
        ])->with('status', "Akun member berhasil dibuat. ID Anda: {$user->member_code}");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
