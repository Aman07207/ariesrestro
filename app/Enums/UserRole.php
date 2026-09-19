<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case HotelAdmin = 'hotel_admin';
    case Manager = 'manager';
    case Waiter = 'waiter';
    case Chef = 'chef';
}
