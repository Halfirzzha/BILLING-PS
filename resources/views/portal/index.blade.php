<x-dynamic-component :component="'layouts.app'" title="Portal Member">
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-400">ID Member</p>
                    <p class="mt-3 text-2xl font-semibold text-white">{{ $member->member_code }}</p>
                </div>
                <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-400">Saldo Wallet</p>
                    <p class="mt-3 text-2xl font-semibold text-white">Rp {{ number_format($member->wallet_balance, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-400">Sisa Waktu</p>
                    <p class="mt-3 text-2xl font-semibold text-white">{{ $timeBalance }} menit</p>
                </div>
            </div>

            @if ($activeSession)
                <div class="rounded-[2rem] border border-emerald-400/20 bg-emerald-500/10 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-emerald-200">Sesi Aktif</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white">{{ $activeSession->station->name }}</h2>
                            <p class="mt-2 text-sm text-emerald-100">Mulai {{ $activeSession->started_at->format('d M Y H:i') }}</p>
                            <p class="mt-2 text-sm text-white">Sisa waktu sesi: {{ $activeSession->remainingSessionMinutes() }} menit</p>
                        </div>

                        <form action="{{ route('portal.sessions.destroy', $activeSession) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full bg-white px-5 py-3 font-medium text-stone-950">Akhiri Sesi</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Pilih Station</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">{{ $station?->name ?? 'Belum memilih station' }}</h2>
                    </div>

                    @if ($station && ! $activeSession)
                        <form action="{{ route('portal.sessions.store', $station) }}" method="POST">
                            @csrf
                    <button class="rounded-full bg-amber-400 px-5 py-3 font-medium text-stone-950" @disabled($timeBalance <= 0)>
                                Mulai Sesi
                            </button>
                        </form>
                    @endif
                </div>

                @if (! $station)
                    <p class="mt-4 text-sm leading-7 text-stone-300">Scan QR di TV Android atau buka link station dari halaman utama untuk memilih perangkat.</p>
                @elseif ($timeBalance <= 0)
                    <p class="mt-4 text-sm leading-7 text-amber-200">Waktu Anda habis. Beli paket di bawah atau lakukan pembayaran cash melalui operator.</p>
                @else
                    <p class="mt-4 text-sm leading-7 text-stone-300">Station siap dipakai. Saat sesi aktif, operator dapat memantau progres dari panel admin.</p>
                @endif
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Paket Waktu</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Beli via Wallet</h2>
                </div>
                <span class="rounded-full border border-white/10 px-3 py-1 text-xs text-stone-300">Cash via operator</span>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($packages as $package)
                    <form action="{{ route('portal.packages.purchase', $package) }}" method="POST" class="rounded-2xl border border-white/10 bg-black/20 p-5">
                        @csrf
                        <input type="hidden" name="payment_method" value="wallet">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $package->name }}</h3>
                                <p class="mt-1 text-sm text-stone-400">{{ $package->minutes }} menit</p>
                                @if ($package->description)
                                    <p class="mt-3 text-sm leading-7 text-stone-300">{{ $package->description }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-amber-200">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                                <button class="mt-4 rounded-full border border-amber-300/20 px-4 py-2 text-sm text-amber-200">Beli</button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>
        </div>
    </section>
</x-dynamic-component>
