<x-dynamic-component :component="'layouts.app'" title="Daftar Member">
    <section class="mx-auto max-w-2xl rounded-[2rem] border border-white/10 bg-white/5 p-8 backdrop-blur">
        <h1 class="text-3xl font-semibold text-white">Daftar Member Baru</h1>
        <p class="mt-3 text-sm leading-7 text-stone-300">
            Setelah registrasi, akun member bisa top up, beli paket waktu, dan dipakai scan QR di station mana pun.
        </p>

        <form action="{{ route('member.register.store') }}" method="POST" class="mt-8 grid gap-5 md:grid-cols-2">
            @csrf
            <input type="hidden" name="station" value="{{ $station }}">

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm text-stone-300">Nama</label>
                <input name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white" required>
            </div>

            <div>
                <label class="mb-2 block text-sm text-stone-300">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white" required>
            </div>

            <div>
                <label class="mb-2 block text-sm text-stone-300">No. HP</label>
                <input name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white" required>
            </div>

            <div>
                <label class="mb-2 block text-sm text-stone-300">Password</label>
                <input type="password" name="password" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white" required>
            </div>

            <div>
                <label class="mb-2 block text-sm text-stone-300">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-white" required>
            </div>

            <div class="md:col-span-2">
                <button class="w-full rounded-2xl bg-amber-400 px-4 py-3 font-medium text-stone-950">Buat Akun Member</button>
            </div>
        </form>
    </section>
</x-dynamic-component>
