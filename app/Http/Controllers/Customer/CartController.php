<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;

class CartController extends Controller
{
    use ResolvesCustomerSession;

    public function show()
    {
        return view('customer.cart', [
            'table' => $this->currentTable(),
            'memberNo' => $this->currentMemberNo(),
        ]);
    }
}
