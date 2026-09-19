<?php

namespace App\Services\SuperAdmin;

use App\Models\PlatformSetting;

class PlatformSettingService
{
    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            PlatformSetting::set($key, $value);
        }
    }
}
