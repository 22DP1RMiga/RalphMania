<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of categories for admin.
     */
    public function index()
    {
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('is_active', true);
        }])
            ->orderBy('sort_order')
            ->orderBy('name_lv')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name_lv' => $category->name_lv,
                    'name_en' => $category->name_en,
                    'slug' => $category->slug,
                    'description_lv' => $category->description_lv,
                    'description_en' => $category->description_en,
                    'icon' => $category->icon,
                    'parent_id' => $category->parent_id,
                    'sort_order' => $category->sort_order,
                    'is_active' => $category->is_active,
                    'products_count' => $category->products_count,
                    'created_at' => $category->created_at,
                ];
            });

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_lv' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name_en']);
        }

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Set default sort order
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Category::max('sort_order') + 1;
        }

        Category::create($validated);

        return back()->with('success', 'Kategorija veiksmīgi izveidota!');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name_lv' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:categories,slug,' . $id,
            'description_lv' => 'nullable|string',
            'description_en' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Prevent setting self as parent
        if ($validated['parent_id'] == $id) {
            return back()->withErrors(['parent_id' => 'Kategorija nevar būt sev pašai vecākkategorija.']);
        }

        $category->update($validated);

        return back()->with('success', 'Kategorija veiksmīgi atjaunināta!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        // Check if category has products
        if ($category->products_count > 0) {
            return back()->withErrors(['error' => 'Nevar dzēst kategoriju ar produktiem. Vispirms pārvietojiet vai dzēsiet produktus.']);
        }

        // Check if category has subcategories
        if (Category::where('parent_id', $id)->exists()) {
            return back()->withErrors(['error' => 'Nevar dzēst kategoriju ar apakškategorijām.']);
        }

        $category->delete();

        return back()->with('success', 'Kategorija veiksmīgi dzēsta!');
    }
}
