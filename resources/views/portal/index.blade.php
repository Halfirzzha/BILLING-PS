@extends('layouts.portal')

@section('title', 'Portal Member — Billing PS5')

@section('content')
    <div class="mb-2 text-sm text-slate-400">Halo, {{ $user->name }} · {{ $user->member_code }}</div>

    {{-- Balances --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-5">
            <div class="text-xs uppercase tracking-wide text-slate-400">Saldo Wallet</div>
            <div class="mt-1 text-2xl font-bold">Rp {{ number_format($user->wallet_balance, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-5">
            <div class="text-xs uppercase tracking-wide text-slate-400">Saldo Waktu</div>
            <div class="mt-1 text-2xl font-bold">{{ $user->remaining_minutes }} <span class="text-base font-normal text-slate-400">menit</span></div>
        </div>
    </div>

    {{-- Active session --}}
    @if ($activeSession)
        <div class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wide text-emerald-300">Sedang Bermain</div>
                    <div class="mt-1 text-lg font-semibold">{{ $activeSession->station->name }}</div>
                </div>
                <div id="countdown" class="text-3xl font-bold tabular-nums" data-end="{{ optional($activeSession->planned_end_at)->toIso8601String() }}">--:--</div>
            </div>
            <form method="POST" action="{{ route('portal.session.end') }}" class="mt-4">
                @csrf
                <button class="rounded-xl bg-rose-500 px-4 py-2 font-semibold text-white hover:bg-rose-400">Akhiri Sesi</button>
            </form>
        </div>
    @elseif ($station)
        {{-- Joined a station, not yet playing --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900/50 p-5">
            <div class="text-xs uppercase tracking-wide text-slate-400">Station Dipilih</div>
            <div class="mt-1 text-lg font-semibold">{{ $station->name }} <span class="text-slate-500">· {{ $station->code }}</span></div>
            @if ($user->remaining_minutes > 0)
                <form method="POST" action="{{ route('portal.session.start') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="station_id" value="{{ $station->id }}">
                    <button class="rounded-xl bg-amber-500 px-5 py-2.5 font-semibold text-slate-950 hover:bg-amber-400">
                        Mulai Bermain di {{ $station->name }}
                    </button>
                </form>
            @else
                <p class="mt-3 text-sm text-amber-300">Saldo waktu habis. Beli paket dulu untuk mulai bermain.</p>
            @endif
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900/50 p-5 text-sm text-slate-400">
            Scan QR di station untuk memilih tempat bermain.
        </div>
    @endif

    {{-- Packages --}}
    <h2 class="mt-8 mb-3 text-lg font-semibold">Beli Paket Waktu</h2>
    <div class="grid gap-3 sm:grid-cols-2">
        @forelse ($packages as $package)
            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                <div>
                    <div class="font-semibold">{{ $package->name }}</div>
                    <div class="text-sm text-slate-400">{{ $package->minutes }} menit · Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                </div>
                <form method="POST" action="{{ route('portal.purchase', $package) }}">
                    @csrf
                    <button class="rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/20"
                        @disabled($user->wallet_balance < $package->price)>
                        {{ $user->wallet_balance < $package->price ? 'Saldo kurang' : 'Beli (Wallet)' }}
                    </button>
                </form>
            </div>
        @empty
            <p class="text-sm text-slate-400">Belum ada paket tersedia.</p>
        @endforelse
    </div>
    <p class="mt-3 text-xs text-slate-500">Top up saldo wallet dilakukan di kasir/operator.</p>
@endsection

@section('scripts')
<script>
    const el = document.getElementById('countdown');
    if (el && el.dataset.end) {
        const end = new Date(el.dataset.end).getTime();
        const tick = () => {
            const diff = Math.max(0, Math.floor((end - Date.now()) / 1000));
            const m = String(Math.floor(diff / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            el.textContent = m + ':' + s;
            if (diff <= 0) { setTimeout(() => location.reload(), 3000); return; }
            setTimeout(tick, 1000);
        };
        tick();
    }
</script>
@endsection
