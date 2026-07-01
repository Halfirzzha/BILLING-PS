<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Billing PS5' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-950 text-stone-100">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.25),transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(15,23,42,0.8),transparent_35%)]"></div>

    <header class="border-b border-white/10 bg-black/30 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-[0.24em] text-amber-300">BILLING PS5</a>

            <nav class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('portal.index') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Portal Member</a>
                    @if (auth()->user()->hasAnyRole(['super_admin', 'operator']))
                        <a href="{{ url('/admin') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Admin</a>
                    @endif
                    <form action="{{ route('member.logout') }}" method="POST">
                        @csrf
                        <button class="rounded-full bg-amber-400 px-4 py-2 font-medium text-stone-950">Logout</button>
                    </form>
                @else
                    <a href="{{ route('member.login') }}" class="rounded-full border border-white/15 px-4 py-2 text-stone-200 transition hover:border-amber-300 hover:text-amber-200">Login Member</a>
                    <a href="{{ route('member.register') }}" class="rounded-full bg-amber-400 px-4 py-2 font-medium text-stone-950">Daftar Member</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-5 py-4 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-5 py-4 text-sm text-rose-200">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
