<x-dynamic-component :component="'layouts.app'" title="Login Member">
    <section class="mx-auto max-w-xl rounded-[2rem] border border-white/10 bg-white/5 p-8 backdrop-blur">
        <h1 class="text-3xl font-semibold text-white">Login Member</h1>
        <p class="mt-3 text-sm leading-7 text-stone-300">
            Masukkan ID member, email, atau nomor telepon untuk melanjutkan ke portal billing.
        </p>

        <form action="{{ route('member.login.store') }}" method="POST" class="mt-8 space-y-5">
            @csrf
            <input type="hidden" name="station" value="{{ $station }}">

            <div>
                <label class="mb-2 block text-sm text-stone-300">ID Member / Email / No. HP</label>
                <input name="login" value="{{ old('login') }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none ring-0" required>
            </div>

            <div>
                <label class="mb-2 block text-sm text-stone-300">Password</label>
                <input type="password" name="password" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none ring-0" required>
            </div>

            <button class="w-full rounded-2xl bg-amber-400 px-4 py-3 font-medium text-stone-950">Masuk</button>
        </form>

        <p class="mt-6 text-sm text-stone-400">
            Belum punya akun?
            <a href="{{ route('member.register', ['station' => $station]) }}" class="text-amber-300">Daftar member baru</a>
        </p>
    </section>
</x-dynamic-component>
