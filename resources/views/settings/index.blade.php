@extends('layouts.app')
@section('title', 'Settings')
@section('header', 'Business Settings')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-bold text-white mb-6">Business Settings</h1>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data"
          class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Business Name <span class="text-red-400">*</span></label>
                <input type="text" name="business_name" value="{{ old('business_name', $settings->business_name) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('business_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">GSTIN</label>
                <input type="text" name="gstin" value="{{ old('gstin', $settings->gstin) }}" maxlength="15"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">PAN</label>
                <input type="text" name="pan" value="{{ old('pan', $settings->pan) }}" maxlength="10"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $settings->email) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Address</label>
                <textarea name="address" rows="2"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $settings->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Currency</label>
                <input type="text" name="currency" value="{{ old('currency', $settings->currency ?? '₹') }}" maxlength="5"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Timezone</label>
                <input type="text" name="timezone" value="{{ old('timezone', $settings->timezone ?? 'Asia/Kolkata') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Invoice Prefix</label>
                <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Terms &amp; Conditions</label>
                <textarea name="terms" rows="2"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('terms', $settings->terms) }}</textarea>
            </div>

            <div class="sm:col-span-2 border-t border-slate-800 pt-5">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Payment Settings</h3>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">UPI ID</label>
                <input type="text" name="upi_id" value="{{ old('upi_id', $settings->upi_id) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">UPI Payee Name</label>
                <input type="text" name="payee_name" value="{{ old('payee_name', $settings->payee_name) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Bank Account No</label>
                <input type="text" name="bank_account" value="{{ old('bank_account', $settings->bank_account) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">IFSC</label>
                <input type="text" name="ifsc" value="{{ old('ifsc', $settings->ifsc) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="show_qr_on_invoice" value="0">
                    <input type="checkbox" name="show_qr_on_invoice" value="1" {{ $settings->show_qr_on_invoice ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-indigo-600 w-4 h-4">
                    <span class="text-sm text-slate-300">Show QR code on invoice PDF</span>
                </label>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Business Logo</label>
                <input type="file" name="logo" accept="image/*"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-400 rounded-lg px-3 py-2 text-sm file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-600 file:text-white">
                @if($settings->logo)
                <p class="text-xs text-slate-500 mt-1">Current logo: <span class="text-slate-400">{{ $settings->logo }}</span></p>
                @endif
            </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-800">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
