<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelAdmin\MenuCategoryRequest;
use App\Models\MenuCategory;
use App\Services\HotelAdmin\MenuCategoryService;

class MenuCategoryController extends Controller
{
    public function __construct(private readonly MenuCategoryService $categories)
    {
    }

    public function index()
    {
        return view('hoteladmin.menu-categories.index', [
            'categories' => MenuCategory::withCount('menuItems')->orderBy('display_order')->get(),
        ]);
    }

    public function create()
    {
        return view('hoteladmin.menu-categories.create');
    }

    public function store(MenuCategoryRequest $request)
    {
        $this->categories->create($request->validated());

        return redirect()->route('hoteladmin.menu-categories.index')->with('status', 'Category added.');
    }

    public function edit(MenuCategory $menuCategory)
    {
        return view('hoteladmin.menu-categories.edit', ['category' => $menuCategory]);
    }

    public function update(MenuCategoryRequest $request, MenuCategory $menuCategory)
    {
        $this->categories->update($menuCategory, $request->validated());

        return redirect()->route('hoteladmin.menu-categories.index')->with('status', 'Category updated.');
    }

    public function destroy(MenuCategory $menuCategory)
    {
        if (! $this->categories->delete($menuCategory)) {
            return redirect()->route('hoteladmin.menu-categories.index')
                ->with('status', 'This category has items with past orders and can\'t be removed — remove or reassign those items first.');
        }

        return redirect()->route('hoteladmin.menu-categories.index')->with('status', 'Category removed.');
    }
}
