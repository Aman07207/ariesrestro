<?php

namespace App\Services\SuperAdmin;

use App\Models\Subscription;

class SubscriptionService
{
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription;
    }

    public function delete(Subscription $subscription): void
    {
        $subscription->delete();
    }
}
