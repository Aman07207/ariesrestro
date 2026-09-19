<?php

namespace App\Services;

use App\Models\DemoRequest;

class DemoRequestService
{
    public function create(array $data): DemoRequest
    {
        return DemoRequest::create($data);
    }
}
