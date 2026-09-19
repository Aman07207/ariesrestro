<?php

namespace App\Http\Controllers\Customer\Concerns;

use App\Models\OrderItem;
use App\Models\OrderSession;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request;

/**
 * Reads the session/table/member the `resolve.session` middleware (ResolveTableSession)
 * already resolved and attached to the request from the customer's session cookie.
 */
trait ResolvesCustomerSession
{
    protected function currentTable(): Table
    {
        return Request::instance()->attributes->get('customerTable');
    }

    protected function currentSession(): OrderSession
    {
        return Request::instance()->attributes->get('customerSession');
    }

    protected function currentMemberNo(): int
    {
        return Request::instance()->attributes->get('customerMemberNo');
    }

    /** @return Collection<int, OrderItem> */
    protected function currentOrderItems(): Collection
    {
        $session = $this->currentSession();

        return OrderItem::whereHas('order', fn ($q) => $q->where('session_id', $session->id))
            ->with(['order.table'])
            ->get();
    }
}
