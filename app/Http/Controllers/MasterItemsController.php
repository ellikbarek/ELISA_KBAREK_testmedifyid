<?php

namespace App\Http\Controllers;

use App\Models\MasterItem;
use App\Models\Category;
use App\Services\ItemSpreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasterItemsController extends Controller
{
    public function index()
    {
        return view('master_items.index.index');
    }

    public function search(Request $request)
    {
        $data_search = $this->filteredItems($request)->with('categories:id,nama')->orderBy('id')->get();
        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        abort_unless(in_array($method, ['new', 'edit']), 404);
        if ($method == 'new') {
            $item = new MasterItem;
        } else {
            $item = MasterItem::with('categories')->findOrFail($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        $data['categories'] = Category::orderBy('nama')->get();
        return view('master_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = MasterItem::with('categories')->where('kode', $kode)->firstOrFail();
        return view('master_items.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        abort_unless(in_array($method, ['new', 'edit']), 404);
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli' => 'required|integer|min:0|max:2147483647',
            'laba' => 'required|integer|min:0|max:2147483647',
            'supplier' => 'required|string|in:Tokopaedi,Bukulapuk,TokoBagas,E Commurz,Blublu',
            'jenis' => 'required|string|in:Obat,Alkes,Matkes,Umum,ATK',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hapus_foto' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|distinct|exists:categories,id',
        ]);
        $item = $method === 'new' ? new MasterItem : MasterItem::findOrFail($id);
        $oldPhoto = $item->foto;
        $newPhoto = $request->hasFile('foto') ? $request->file('foto')->store('item-photos') : null;
        if ($request->hasFile('foto') && !$newPhoto) {
            throw \Illuminate\Validation\ValidationException::withMessages(['foto' => 'Foto gagal disimpan. Silakan coba lagi.']);
        }

        try {
            DB::transaction(function () use ($item, $validated, $request, $newPhoto) {
                foreach (['nama', 'harga_beli', 'laba', 'supplier', 'jenis'] as $field) {
                    $item->{$field} = $validated[$field];
                }
                $isNew = !$item->exists;
                if ($isNew) $item->kode = 'pending';
                if ($newPhoto) $item->foto = $newPhoto;
                elseif ($request->boolean('hapus_foto')) $item->foto = null;
                $item->save();
                if ($isNew) {
                    $item->kode = str_pad($item->id, 5, '0', STR_PAD_LEFT);
                    $item->save();
                }
                $item->categories()->sync($validated['categories'] ?? []);
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) Storage::delete($newPhoto);
            throw $exception;
        }
        if ($oldPhoto && $oldPhoto !== $item->foto) Storage::delete($oldPhoto);
        return redirect('master-items')->with('success', 'Item berhasil disimpan.');
    }

    public function delete($id)
    {
        $item = MasterItem::findOrFail($id);
        DB::transaction(function () use ($item) {
            $item->categories()->detach();
            $item->delete();
        });
        if ($item->foto) Storage::delete($item->foto);
        return redirect('master-items')->with('success', 'Item berhasil dihapus.');
    }

    public function photo(MasterItem $item)
    {
        abort_unless($item->foto && Storage::exists($item->foto), 404);
        return Storage::response($item->foto);
    }

    public function export(Request $request, ItemSpreadsheet $spreadsheet)
    {
        $items = $this->filteredItems($request)->with('categories')->orderBy('id')->get();
        $path = $spreadsheet->create($items);
        return response()->download($path, 'master-items.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function filteredItems(Request $request)
    {
        $request->validate([
            'kode' => 'nullable|string|max:255',
            'nama' => 'nullable|string|max:255',
            'hargamin' => 'nullable|numeric|min:0',
            'hargamax' => ['nullable', 'numeric', 'min:0', ...($request->filled('hargamin') ? ['gte:hargamin'] : [])],
        ]);
        return MasterItem::query()
            ->when($request->filled('kode'), fn ($query) => $query->where('kode', $request->kode))
            ->when($request->filled('nama'), fn ($query) => $query->where('nama', 'like', '%'.$request->nama.'%'))
            ->when($request->filled('hargamin'), fn ($query) => $query->where('harga_beli', '>=', $request->hargamin))
            ->when($request->filled('hargamax'), fn ($query) => $query->where('harga_beli', '<=', $request->hargamax));
    }

}
