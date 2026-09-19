<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Active = 'active';
    case BillRequested = 'bill_requested';
    case Paid = 'paid';
    case Closed = 'closed';
    case Expired = 'expired';
}
