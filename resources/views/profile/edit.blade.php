@extends('layouts.app')
@section('title', 'Profile')
@section('header', 'My Profile')

@section('content')
<div class="max-w-2xl space-y-6">
    <h1 class="text-xl font-bold text-white">Profile Settings</h1>

    @if($status === 'profile-updated')
    <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-lg text-sm">Profile updated successfully.</div>
    @endif

    {{-- Update Profile --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
        <h3 class="text-sm font-semibold text-slate-300">Profile Information</h3>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                       class="w-full bg-slate-800 border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                       class="w-full bg-slate-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @if($mustVerifyEmail && !auth()->user()->hasVerifiedEmail())
            <div class="bg-amber-900/30 border border-amber-700 text-amber-300 px-3 py-2 rounded-lg text-xs">
                Your email address is unverified.
                <form method="POST" action="{{ route('verification.send') }}" class="inline">
                    @csrf
                    <button type="submit" class="underline hover:text-amber-200">Click here to resend the verification email.</button>
                </form>
            </div>
            @endif

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
        <h3 class="text-sm font-semibold text-slate-300">Change Password</h3>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Current Password</label>
                <input type="password" name="current_password"
                       class="w-full bg-slate-800 border {{ $errors->has('current_password') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('current_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">New Password</label>
                <input type="password" name="password"
                       class="w-full bg-slate-800 border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Confirm New Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                Update Password
            </button>
        </form>
    </div>

    {{-- Delete Account --}}
    <div class="bg-slate-900 border border-red-900/50 rounded-xl p-6 space-y-4">
        <h3 class="text-sm font-semibold text-red-400">Danger Zone</h3>
        <p class="text-xs text-slate-500">Permanently delete your account. This action cannot be undone.</p>
        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete your account? This cannot be undone.')">
            @csrf @method('DELETE')
            <div class="mb-3">
                <label class="block text-xs text-slate-400 mb-1">Confirm Password</label>
                <input type="password" name="password"
                       class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 w-60">
                @error('password', 'userDeletion') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                Delete Account
            </button>
        </form>
    </div>
</div>
@endsection
