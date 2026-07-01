@extends('layouts.portal')

@section('title', 'Daftar — Billing PS5')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-white/10 bg-slate-900/50 p-6">
        <h1 class="text-xl font-bold">Daftar Member</h1>
        <p class="mt-1 text-sm text-slate-400">Buat akun untuk mulai bermain.</p>

        <form method="POST" action="{{ route('register.attempt') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-slate-300">Nama</label>
                <input name="name" type="text" value="{{ old('name') }}" required autofocus
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm text-slate-300">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm text-slate-300">No. HP <span class="text-slate-500">(opsional)</span></label>
                <input name="phone" type="text" value="{{ old('phone') }}"
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm text-slate-300">Password</label>
                <input name="password" type="password" required
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm text-slate-300">Ulangi Password</label>
                <input name="password_confirmation" type="password" required
                    class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 outline-none focus:border-amber-500">
            </div>
            <button class="w-full rounded-xl bg-amber-500 px-4 py-2.5 font-semibold text-slate-950 hover:bg-amber-400">Daftar</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-400">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-amber-400 hover:underline">Masuk</a>
        </p>
    </div>
@endsection
