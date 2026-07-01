<x-dynamic-component :component="'layouts.app'" title="Join Station">
    <section class="mx-auto max-w-3xl rounded-[2rem] border border-white/10 bg-white/5 p-8 text-center backdrop-blur">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-200">Station Terdeteksi</p>
        <h1 class="mt-4 text-4xl font-semibold text-white">{{ $station->name }}</h1>
        <p class="mt-3 text-base leading-8 text-stone-300">
            Login jika sudah member. Jika belum punya akun, daftar dulu lalu kembali ke station ini secara otomatis.
        </p>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('member.login', ['station' => $station->code]) }}" class="rounded-full border border-white/15 px-5 py-3 text-stone-200">Login Member</a>
            <a href="{{ route('member.register', ['station' => $station->code]) }}" class="rounded-full bg-amber-400 px-5 py-3 font-medium text-stone-950">Daftar Member</a>
        </div>
    </section>
</x-dynamic-component>
