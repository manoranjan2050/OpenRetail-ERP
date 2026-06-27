@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('header', isset($customer) ? 'Edit Customer' : 'Add Customer')

@section('content')
<div class="max-w-2xl mx-auto">
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5">
      <h2 class="text-white font-bold text-lg">{{ isset($customer) ? '✏️ Edit Customer' : '➕ Add New Customer' }}</h2>
      <p class="text-indigo-200 text-sm mt-0.5">{{ isset($customer) ? 'Update customer details' : 'Fill in customer information' }}</p>
    </div>

    <form method="POST"
      action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}"
      enctype="multipart/form-data"
      class="p-6 space-y-5">
      @csrf
      @if(isset($customer)) @method('PUT') @endif

      @if($errors->any())
      <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      {{-- Photo --}}
      <div x-data="{ preview: '{{ isset($customer) && $customer->photo ? Storage::url($customer->photo) : '' }}' }">
        <label class="block text-sm font-semibold text-slate-700 mb-2">Photo</label>
        <div class="flex items-center gap-4">
          <div class="w-20 h-20 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 overflow-hidden flex items-center justify-center shrink-0">
            <template x-if="preview"><img :src="preview" class="w-full h-full object-cover"></template>
            <template x-if="!preview"><span class="text-3xl">👤</span></template>
          </div>
          <div>
            <input type="file" name="photo" accept="image/*"
              class="block text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold hover:file:bg-indigo-100 transition-colors"
              @change="preview = URL.createObjectURL($event.target.files[0])">
            <p class="text-xs text-slate-400 mt-1">JPG, PNG · max 2MB</p>
          </div>
        </div>
      </div>

      {{-- Name + Type --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required
            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
            placeholder="Customer name">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Customer Type</label>
          <select name="customer_type" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors bg-white">
            @foreach(['retail'=>'🛒 Retail','wholesale'=>'🏭 Wholesale','distributor'=>'🚚 Distributor','vip'=>'⭐ VIP'] as $val => $label)
            <option value="{{ $val }}" {{ old('customer_type', $customer->customer_type ?? 'retail') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Mobile + Email --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mobile</label>
          <input type="tel" name="mobile" value="{{ old('mobile', $customer->mobile ?? '') }}"
            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
            placeholder="+91 9999999999">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
          <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors"
            placeholder="email@example.com">
        </div>
      </div>

      {{-- Address --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
        <textarea name="address" rows="2" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors resize-none" placeholder="Street, city, state, PIN">{{ old('address', $customer->address ?? '') }}</textarea>
      </div>

      {{-- GSTIN + Credit Limit --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">GSTIN</label>
          <input type="text" name="gstin" value="{{ old('gstin', $customer->gstin ?? '') }}" maxlength="15"
            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors font-mono uppercase"
            placeholder="22AAAAA0000A1Z5">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Credit Limit (₹)</label>
          <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}" min="0" step="0.01"
            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors">
        </div>
      </div>

      {{-- Billing Cycle --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Billing Cycle</label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
          @foreach(['none'=>'None','weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly'] as $val => $label)
          <label class="flex items-center justify-center border-2 rounded-xl py-2.5 px-3 cursor-pointer transition-all {{ old('billing_cycle', $customer->billing_cycle ?? 'none') === $val ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
            <input type="radio" name="billing_cycle" value="{{ $val }}" class="sr-only" {{ old('billing_cycle', $customer->billing_cycle ?? 'none') === $val ? 'checked' : '' }}
              onclick="document.querySelectorAll('[data-cycle]').forEach(el=>{el.className=el.className.replace('border-indigo-500 bg-indigo-50 text-indigo-700','border-slate-200 text-slate-600')});this.closest('label').className=this.closest('label').className.replace('border-slate-200 text-slate-600','border-indigo-500 bg-indigo-50 text-indigo-700')">
            <span class="text-sm font-medium">{{ $label }}</span>
          </label>
          @endforeach
        </div>
      </div>

      {{-- Buttons --}}
      <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors shadow-sm">
          {{ isset($customer) ? '💾 Update Customer' : '✅ Save Customer' }}
        </button>
        <a href="{{ route('customers.index') }}" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-xl text-sm transition-colors">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
