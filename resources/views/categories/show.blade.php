@extends('layouts.app')
@section('content')
<div class="container"><div class="row justify-content-center"><div class="col-md-8">
    <div class="form-group mb-2"><a class="btn btn-secondary" href="{{ route('categories.index') }}">Kembali ke Daftar Kategori</a></div>
    <div class="card">
        <div class="card-header">Kategori Items</div>
        <div class="card-body">
            <table class="table"><tr><th>Nama</th><td>{{ $category->nama }}</td></tr><tr><th>Kode</th><td>{{ $category->kode }}</td></tr></table>
            <div class="d-flex gap-2 mb-3">
                <a class="btn btn-info" href="{{ route('categories.edit', $category) }}">Edit</a>
                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini? Item tetap tersimpan.');">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>
                <a class="btn btn-primary" href="{{ route('categories.pdf', $category) }}">Download PDF</a>
            </div>
            <h4>Daftar Item</h4>
            <div class="table-responsive"><table class="table table-striped">
                <thead><tr><th>Kode</th><th>Nama</th><th>Supplier</th><th>Harga Beli</th><th>Laba (%)</th><th>Harga Jual</th></tr></thead>
                <tbody>
                @forelse($category->items as $item)
                    <tr><td><a href="{{ url('master-items/view/'.$item->kode) }}">{{ $item->kode }}</a></td><td>{{ $item->nama }}</td><td>{{ $item->supplier }}</td><td>{{ number_format($item->harga_beli, 0, ',', '.') }}</td><td>{{ $item->laba }}</td><td>{{ number_format($item->harga_jual, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center">Belum ada item dalam kategori ini.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div></div></div>
@endsection
