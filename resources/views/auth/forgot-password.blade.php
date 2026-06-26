<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>body { font-family: 'Figtree', sans-serif; }</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg mx-auto mb-3">OR</div>
            <h1 class="text-xl font-bold text-white">Reset Password</h1>
            <p class="text-slate-400 text-sm mt-1">Enter your email to receive a reset link</p>
        </div>

        @if($status)
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-lg text-sm mb-4">{{ $status }}</div>
        @endif

        @if($errors->any())
        <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded-lg text-sm mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-slate-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
                Send Reset Link
            </button>

            <p class="text-center text-sm text-slate-500">
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300">Back to login</a>
            </p>
        </form>
    </div>
</body>
</html>
