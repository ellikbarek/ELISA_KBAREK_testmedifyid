<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['nama' => 'nullable|string|max:255', 'kode' => 'nullable|string|max:255']);
        $categories = Category::query()
            ->when($request->filled('nama'), fn ($query) => $query->where('nama', 'like', '%'.$filters['nama'].'%'))
            ->when($request->filled('kode'), fn ($query) => $query->where('kode', 'like', '%'.$filters['kode'].'%'))
            ->withCount('items')->orderBy('kode', 'asc')->paginate(15)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.form', ['category' => new Category]);
    }

    public function store(Request $request)
    {
        $category = Category::create($this->validated($request));
        return redirect()->route('categories.show', $category)->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category)
    {
        $category->load(['items' => fn ($query) => $query->orderBy('nama')]);
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));
        return redirect()->route('categories.show', $category)->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function pdf(Category $category)
    {
        $category->load(['items' => fn ($query) => $query->orderBy('nama')]);
        $printedAt = now('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB';
        return Pdf::loadView('categories.pdf', compact('category', 'printedAt'))
            ->setPaper('a4', 'landscape')->download('kategori-'.$category->id.'.pdf');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:255', Rule::unique('categories', 'kode')->ignore($category?->id)],
        ]);
    }
}
