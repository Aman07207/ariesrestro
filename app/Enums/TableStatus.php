<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'available';
    case Active = 'active';
    case NeedsCleaning = 'needs_cleaning';
    case Disabled = 'disabled';
}
