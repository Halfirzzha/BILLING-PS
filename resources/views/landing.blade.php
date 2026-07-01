@extends('layouts.portal')

@section('title', 'Billing PS5 — Rental PlayStation 5')

@section('content')
    <div class="rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900 to-slate-950 p-8 text-center">
        <span class="inline-block rounded-full bg-amber-500/15 px-3 py-1 text-xs font-medium text-amber-300">
            Rental PS5 + Android TV
        </span>
        <h1 class="mt-4 text-3xl font-bold tracking-tight">Main sekarang, bayar pakai saldo waktu.</h1>
        <p class="mx-auto mt-3 max-w-md text-slate-400">
            Scan QR di station, top up saldo, beli paket waktu, dan langsung bermain.
            Saldo waktu bisa dipakai di outlet mana saja.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ route('portal') }}" class="rounded-xl bg-amber-500 px-5 py-2.5 font-semibold text-slate-950 hover:bg-amber-400">Buka Portal</a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl bg-amber-500 px-5 py-2.5 font-semibold text-slate-950 hover:bg-amber-400">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-xl border border-white/15 px-5 py-2.5 font-semibold hover:bg-white/5">Daftar</a>
            @endauth
        </div>
    </div>
@endsection
