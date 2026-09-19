<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelAdmin\MenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\HotelAdmin\MenuItemService;

class MenuItemController extends Controller
{
    public function __construct(private readonly MenuItemService $menuItems)
    {
    }

    public function index()
    {
        return view('hoteladmin.menu-items.index', [
            'items' => MenuItem::with('category')->orderBy('name')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('hoteladmin.menu-items.create', ['categories' => MenuCategory::orderBy('name')->get()]);
    }

    public function store(MenuItemRequest $request)
    {
        $this->menuItems->create($request->validated(), $request->file('image'));

        return redirect()->route('hoteladmin.menu-items.index')->with('status', 'Menu item added.');
    }

    public function edit(MenuItem $menuItem)
    {
        return view('hoteladmin.menu-items.edit', [
            'item' => $menuItem,
            'categories' => MenuCategory::orderBy('name')->get(),
        ]);
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem)
    {
        $this->menuItems->update($menuItem, $request->validated(), $request->file('image'));

        return redirect()->route('hoteladmin.menu-items.index')->with('status', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem)
    {
        if (! $this->menuItems->delete($menuItem)) {
            return redirect()->route('hoteladmin.menu-items.index')
                ->with('status', 'This item has past orders and can\'t be deleted — marked unavailable instead.');
        }

        return redirect()->route('hoteladmin.menu-items.index')->with('status', 'Menu item removed.');
    }
}
