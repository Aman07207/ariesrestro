<?php

namespace App\Enums;

/**
 * Which tax law applies to a menu item. GST and alcohol VAT are taxed under
 * completely different laws (alcohol is constitutionally outside GST, taxed by
 * the state instead) and must never be blended into one "tax" figure.
 */
enum TaxTrack: string
{
    case Gst = 'gst';
    case Vat = 'vat';
}
