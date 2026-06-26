<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>body { font-family: 'Figtree', sans-serif; }</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm text-center">
        <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg mx-auto mb-6">OR</div>
        <h1 class="text-xl font-bold text-white mb-2">Verify Your Email</h1>
        <p class="text-slate-400 text-sm mb-6">
            Thanks for signing up! Please verify your email address by clicking the link we sent to you.
        </p>

        @if($status === 'verification-link-sent')
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-lg text-sm mb-4">
            A new verification link has been sent to your email.
        </div>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 py-2.5 rounded-lg transition-colors text-sm">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</body>
</html>
