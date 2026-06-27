<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CategoryService $categoryService,
    ) {
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $shop = auth()->user()->shop;

        $this->categoryService->create($shop, $request->validated());

        return redirect()
            ->route('settings.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categoryService->update($category, $request->validated());

        return redirect()
            ->route('settings.index')
            ->with('success', 'Kategori berhasil diubah.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
