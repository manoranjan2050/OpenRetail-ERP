<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Units/Index', ['units' => Unit::orderBy('name')->get()]);
    }

    public function create(): Response
    {
        return Inertia::render('Units/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100', 'symbol' => 'required|string|max:10']);
        Unit::create($request->only('name', 'symbol'));
        return redirect()->route('units.index')->with('success', 'Unit created.');
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Units/Form', ['unit' => $unit]);
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
