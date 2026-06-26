@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('header', isset($customer) ? 'Edit Customer' : 'Add Customer')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Customers</a>
    </div>

    <form method="POST"
          action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}"
          class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
        @csrf
        @if(isset($customer)) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Full Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}"
                       class="w-full bg-slate-800 border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Mobile</label>
                <input type="tel" name="mobile" value="{{ old('mobile', $customer->mobile ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Address</label>
                <textarea name="address" rows="2"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $customer->address ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Credit Limit (₹)</label>
                <input type="number" name="credit_limit" step="0.01" min="0"
                       value="{{ old('credit_limit', $customer->credit_limit ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Billing Cycle <span class="text-red-400">*</span></label>
                <select name="billing_cycle" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="none" {{ old('billing_cycle', $customer->billing_cycle ?? 'none') == 'none' ? 'selected' : '' }}>None</option>
                    <option value="weekly" {{ old('billing_cycle', $customer->billing_cycle ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('billing_cycle', $customer->billing_cycle ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-800">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
            </button>
            <a href="{{ route('customers.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm px-5 py-2 rounded-lg transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
