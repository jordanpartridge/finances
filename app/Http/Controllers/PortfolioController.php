<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the portfolios.
     */
    public function index(): View
    {
        $portfolios = Portfolio::with('positions')->get();

        return view('portfolios.index', compact('portfolios'));
    }

    /**
     * Show the form for creating a new portfolio.
     */
    public function create(): View
    {
        return view('portfolios.create');
    }

    /**
     * Store a newly created portfolio in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:investment,retirement,trading',
        ]);

        Portfolio::create($validated);

        return redirect()->route('portfolios.index')
            ->with('success', 'Portfolio created successfully.');
    }

    /**
     * Display the specified portfolio.
     */
    public function show(Portfolio $portfolio): View
    {
        $portfolio->load(['positions.transactions']);

        /** @phpstan-ignore argument.type */
        return view('portfolios.show', compact('portfolio'));
    }

    /**
     * Show the form for editing the specified portfolio.
     */
    public function edit(Portfolio $portfolio): View
    {
        /** @phpstan-ignore argument.type */
        return view('portfolios.edit', compact('portfolio'));
    }

    /**
     * Update the specified portfolio in storage.
     */
    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:investment,retirement,trading',
        ]);

        $portfolio->update($validated);

        return redirect()->route('portfolios.index')
            ->with('success', 'Portfolio updated successfully.');
    }

    /**
     * Remove the specified portfolio from storage.
     */
    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->delete();

        return redirect()->route('portfolios.index')
            ->with('success', 'Portfolio deleted successfully.');
    }
}
