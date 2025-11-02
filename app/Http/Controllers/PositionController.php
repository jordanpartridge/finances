<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    /**
     * Display a listing of the positions.
     */
    public function index(): View
    {
        $positions = Position::with('portfolio')->get();

        return view('positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create(): View
    {
        $portfolios = Portfolio::all();

        return view('positions.create', compact('portfolios'));
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'portfolio_id' => 'required|exists:portfolios,id',
            'ticker' => 'required|string|max:10',
            'shares' => 'required|numeric|min:0',
        ]);

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified position.
     */
    public function show(Position $position): View
    {
        $position->load(['portfolio', 'transactions']);

        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position): View
    {
        $portfolios = Portfolio::all();

        return view('positions.edit', compact('position', 'portfolios'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'portfolio_id' => 'required|exists:portfolios,id',
            'ticker' => 'required|string|max:10',
            'shares' => 'required|numeric|min:0',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
