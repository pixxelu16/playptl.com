<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->orderBy('menu_order')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'menu_order' => ['required', 'integer', 'min:0'],
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['string', Rule::in(['single', 'doubles'])],
        ]);

        Category::create([
            'name' => $validated['name'],
            'menu_order' => $validated['menu_order'],
            'type' => implode(',', $validated['types']),
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category created successfully!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'menu_order' => ['required', 'integer', 'min:0'],
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['string', Rule::in(['single', 'doubles'])],
        ]);

        $category->update([
            'name' => $validated['name'],
            'menu_order' => $validated['menu_order'],
            'type' => implode(',', $validated['types']),
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted successfully!');
    }
}
