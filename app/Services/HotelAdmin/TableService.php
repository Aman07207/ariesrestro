<?php

namespace App\Services\HotelAdmin;

use App\Models\Table;

class TableService
{
    public function create(array $data): Table
    {
        // hotel_id auto-fills via the BelongsToHotel trait's creating() hook.
        return Table::create($data);
    }

    public function update(Table $table, array $data): Table
    {
        $table->update($data);

        return $table;
    }

    public function delete(Table $table): void
    {
        $table->delete();
    }
}
