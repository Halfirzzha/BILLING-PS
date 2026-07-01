@extends('layouts.portal')

@section('title', 'Masuk — Billing PS5')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-white/10 bg-slate-900/50 p-6">
        <h1 class="text-xl font-bold">Masuk</h1>
        <p class="mt-1 text-sm text-slate-400">Masuk untuk melihat saldo & mulai bermain.</p>

        <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-slate-300">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm text-slate-300">Password</label>
                <input name="password" type="password" required
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-white/20 bg-slate-950"> Ingat saya
            </label>
            <button class="w-full rounded-xl bg-amber-500 px-4 py-2.5 font-semibold text-slate-950 hover:bg-amber-400">Masuk</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-400">
            Belum punya akun? <a href="{{ route('register') }}" class="text-amber-400 hover:underline">Daftar</a>
        </p>
    </div>
@endsection
