<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionTier;
use App\Http\Requests\Admin\StoreSubscriptionTierRequest;
use App\Http\Requests\Admin\UpdateSubscriptionTierRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class AdminSubscriptionTierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tiers = SubscriptionTier::withTrashed()->orderBy('level')->paginate(10); // Fetching with soft deletes, ordered by level
        return view('admin.subscription-tiers.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.subscription-tiers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionTierRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        // The `is_active` and `max_courses` fields are already prepared in the FormRequest
        // $validatedData['is_active'] = $request->has('is_active');
        // $validatedData['max_courses'] = $request->input('max_courses', null);

        SubscriptionTier::create($validatedData);

        return redirect()->route('admin.subscription-tiers.index')
                         ->with('success', 'Subscription tier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionTier $subscriptionTier): View
    {
        return view('admin.subscription-tiers.show', compact('subscriptionTier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionTier $subscriptionTier): View
    {
        return view('admin.subscription-tiers.edit', compact('subscriptionTier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionTierRequest $request, SubscriptionTier $subscriptionTier): RedirectResponse
    {
        $validatedData = $request->validated();

        // The `is_active` and `max_courses` fields are already prepared in the FormRequest
        // $validatedData['is_active'] = $request->has('is_active');
        // $validatedData['max_courses'] = $request->input('max_courses', null);

        $subscriptionTier->update($validatedData);

        return redirect()->route('admin.subscription-tiers.index')
                         ->with('success', 'Subscription tier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionTier $subscriptionTier): RedirectResponse
    {
        $subscriptionTier->delete(); // Soft delete
        return redirect()->route('admin.subscription-tiers.index')
                         ->with('success', 'Subscription tier soft deleted successfully.');
    }
}
