<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        return view('units.index', ['units' => Unit::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('units.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100', 'symbol' => 'required|string|max:10']);
        Unit::create($request->only('name', 'symbol'));
        return redirect()->route('units.index')->with('success', 'Unit created.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.form', ['unit' => $unit]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100', 'symbol' => 'required|string|max:10']);
        $unit->update($request->only('name', 'symbol'));
        return redirect()->route('units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }
}
