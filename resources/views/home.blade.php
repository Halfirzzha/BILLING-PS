<x-dynamic-component :component="'layouts.app'" title="Billing PS5">
    <section class="grid gap-8 lg:grid-cols-[1.35fr_0.9fr]">
        <div class="space-y-6">
            <div class="inline-flex rounded-full border border-amber-300/30 bg-amber-400/10 px-4 py-1 text-xs uppercase tracking-[0.3em] text-amber-200">
                Rental Control Center
            </div>
            <h1 class="max-w-3xl text-4xl font-semibold leading-tight text-white md:text-6xl">
                Website billing untuk TV Android dan PS5 dengan flow member, QR scan, saldo, dan paket waktu.
            </h1>
            <p class="max-w-2xl text-base leading-8 text-stone-300 md:text-lg">
                Setiap station punya QR sendiri. Member scan, login atau daftar, cek sisa waktu, beli paket, lalu mulai sesi. Operator memantau semua perangkat dari panel admin Filament.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('member.register') }}" class="rounded-full bg-amber-400 px-5 py-3 font-medium text-stone-950">Mulai sebagai member</a>
                <a href="{{ url('/admin') }}" class="rounded-full border border-white/15 px-5 py-3 text-stone-200">Masuk panel admin</a>
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/20 backdrop-blur">
            <h2 class="text-sm uppercase tracking-[0.22em] text-stone-400">Station Aktif</h2>
            <div class="mt-5 space-y-3">
                @forelse ($stations as $station)
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-white">{{ $station->name }}</p>
                                <p class="text-sm text-stone-400">{{ $station->tv_label ?: 'Android TV' }} · {{ $station->ps_label ?: 'PS5' }}</p>
                            </div>
                            <a href="{{ route('stations.display', $station) }}" class="rounded-full border border-amber-300/20 px-4 py-2 text-sm text-amber-200">Lihat QR</a>
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-white/10 p-5 text-sm text-stone-400">Belum ada station aktif. Tambahkan dari panel admin.</p>
                @endforelse
            </div>
        </div>
    </section>
</x-dynamic-component>
