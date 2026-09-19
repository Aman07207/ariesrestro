<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Services\QrCodeService;
use Illuminate\Http\Response;

class TableQrController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCodes)
    {
    }

    public function show(Table $table)
    {
        return view('superadmin.tables.qr', [
            'table' => $table,
            'scanUrl' => $this->qrCodes->scanUrlFor($table),
        ]);
    }

    public function image(Table $table): Response
    {
        return response($this->qrCodes->svgFor($table), 200, ['Content-Type' => 'image/svg+xml']);
    }
}
