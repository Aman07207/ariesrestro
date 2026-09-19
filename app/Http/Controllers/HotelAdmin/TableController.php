<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelAdmin\TableRequest;
use App\Models\Table;
use App\Services\HotelAdmin\TableService;

class TableController extends Controller
{
    public function __construct(private readonly TableService $tables)
    {
    }

    public function index()
    {
        return view('hoteladmin.tables.index', ['tables' => Table::orderBy('table_number')->paginate(15)]);
    }

    public function create()
    {
        return view('hoteladmin.tables.create');
    }

    public function store(TableRequest $request)
    {
        $this->tables->create($request->validated());

        return redirect()->route('hoteladmin.tables.index')->with('status', 'Table added.');
    }

    public function edit(Table $table)
    {
        return view('hoteladmin.tables.edit', ['table' => $table]);
    }

    public function update(TableRequest $request, Table $table)
    {
        $this->tables->update($table, $request->validated());

        return redirect()->route('hoteladmin.tables.index')->with('status', 'Table updated.');
    }

    public function destroy(Table $table)
    {
        $this->tables->delete($table);

        return redirect()->route('hoteladmin.tables.index')->with('status', 'Table removed.');
    }
}
