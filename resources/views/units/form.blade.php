@extends('layouts.app')
@section('title', isset($unit) ? 'Edit Unit' : 'Add Unit')
@section('header', isset($unit) ? 'Edit Unit' : 'Add Unit')

@section('content')
<div class="max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('units.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Units</a>
    </div>

    <form method="POST"
          action="{{ isset($unit) ? route('units.update', $unit) : route('units.store') }}"
          class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
        @csrf
        @if(isset($unit)) @method('PUT') @endif

        <div>
            <label class="block text-xs text-slate-400 mb-1 font-medium">Unit Name <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name', $unit->name ?? '') }}"
                   placeholder="e.g. Kilogram"
                   class="w-full bg-slate-800 border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs text-slate-400 mb-1 font-medium">Symbol <span class="text-red-400">*</span></label>
            <input type="text" name="symbol" value="{{ old('symbol', $unit->symbol ?? '') }}"
                   placeholder="e.g. kg"
                   class="w-full bg-slate-800 border {{ $errors->has('symbol') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('symbol') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-800">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                {{ isset($unit) ? 'Update Unit' : 'Create Unit' }}
            </button>
            <a href="{{ route('units.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm px-5 py-2 rounded-lg transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
