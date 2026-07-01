<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Billing PS5')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-full">
        <header class="border-b border-white/10">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold tracking-tight">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-500 text-slate-950">PS</span>
                    Billing PS5
                </a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-slate-400 hover:text-white">Keluar</button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @yield('scripts')
</body>
</html>
