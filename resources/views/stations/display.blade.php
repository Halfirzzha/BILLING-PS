<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $station->name }} QR</title>
    <meta http-equiv="refresh" content="20">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-stone-950 px-6 text-stone-100">
    <div class="w-full max-w-5xl rounded-[2.5rem] border border-white/10 bg-white/5 p-10 text-center shadow-2xl shadow-black/30 backdrop-blur">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-200">{{ $activeSession ? 'Station Sedang Dipakai' : 'Scan Untuk Mulai' }}</p>
        <h1 class="mt-4 text-5xl font-semibold text-white">{{ $station->name }}</h1>
        <p class="mt-3 text-lg text-stone-300">{{ $station->tv_label ?: 'Android TV' }} · {{ $station->ps_label ?: 'PS5' }}</p>

        @if ($activeSession)
            <div class="mx-auto mt-10 max-w-3xl rounded-[2rem] border border-emerald-400/20 bg-emerald-500/10 p-8">
                <p class="text-sm uppercase tracking-[0.22em] text-emerald-200">Member Aktif</p>
                <h2 class="mt-4 text-4xl font-semibold text-white">{{ $activeSession->user->name }}</h2>
                <p class="mt-3 text-lg text-stone-200">{{ $activeSession->user->member_code }}</p>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Mulai</p>
                        <p class="mt-2 text-xl font-semibold text-white">{{ $activeSession->started_at->format('H:i') }}</p>
                    </div>
                    <div class="rounded-2xl bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Sisa Waktu User</p>
                        <p class="mt-2 text-xl font-semibold text-white">{{ $activeSession->remainingSessionMinutes() }} menit</p>
                    </div>
                    <div class="rounded-2xl bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Status</p>
                        <p class="mt-2 text-xl font-semibold text-white">Sedang Main</p>
                    </div>
                </div>
            </div>
            <p class="mt-8 text-xl text-stone-200">Layar akan kembali ke QR otomatis saat sesi diakhiri.</p>
        @else
            <div class="mx-auto mt-10 flex h-[320px] w-[320px] items-center justify-center rounded-[2rem] bg-white p-6 text-stone-950">
                {!! QrCode::size(280)->generate(route('stations.join', $station->qr_token)) !!}
            </div>

            <p class="mt-8 text-xl text-stone-200">Scan QR, login member, lalu mulai sesi di station ini.</p>
            <p class="mt-2 text-sm text-stone-400">{{ route('stations.join', $station->qr_token) }}</p>
        @endif
    </div>
</body>
</html>
