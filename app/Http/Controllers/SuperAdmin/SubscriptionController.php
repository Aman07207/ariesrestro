<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSubscriptionRequest;
use App\Models\Hotel;
use App\Models\Subscription;
use App\Services\SuperAdmin\SubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions)
    {
    }

    public function index()
    {
        return view('superadmin.subscriptions.index', [
            'subscriptions' => Subscription::with('hotel')->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('superadmin.subscriptions.create', ['hotels' => Hotel::orderBy('name')->get()]);
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $this->subscriptions->create($request->validated());

        return redirect()->route('superadmin.subscriptions.index')->with('status', 'Subscription created.');
    }

    public function edit(Subscription $subscription)
    {
        return view('superadmin.subscriptions.edit', [
            'subscription' => $subscription,
            'hotels' => Hotel::orderBy('name')->get(),
        ]);
    }

    public function update(StoreSubscriptionRequest $request, Subscription $subscription)
    {
        $this->subscriptions->update($subscription, $request->validated());

        return redirect()->route('superadmin.subscriptions.index')->with('status', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription)
    {
        $this->subscriptions->delete($subscription);

        return redirect()->route('superadmin.subscriptions.index')->with('status', 'Subscription removed.');
    }
}
