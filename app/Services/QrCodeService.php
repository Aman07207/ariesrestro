<?php

namespace App\Services;

use App\Models\Table;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function scanUrlFor(Table $table): string
    {
        return route('customer.scan', ['hotel' => $table->hotel->slug, 'table_uuid' => $table->table_uuid]);
    }

    /**
     * SVG rather than PNG: no GD extension in this environment, and SVG scales
     * cleanly for a printed table label anyway.
     */
    public function svgFor(Table $table): string
    {
        $result = (new Builder(writer: new SvgWriter(), data: $this->scanUrlFor($table), size: 320, margin: 10))->build();

        return $result->getString();
    }
}
