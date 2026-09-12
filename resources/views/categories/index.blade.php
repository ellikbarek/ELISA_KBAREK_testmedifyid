@extends('layouts.app')
@section('content')
<div class="container"><div class="row justify-content-center"><div class="col-md-8">
    <div class="form-group mb-2"><a href="{{ route('categories.create') }}" class="btn btn-secondary">+ Kategori Items Baru</a></div>
    <div class="card">
        <div class="card-header">Daftar Kategori Items</div>
        <div class="card-body">
            <h4>Filter</h4>
            <form method="GET" action="{{ route('categories.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-6"><label for="kode">Kode</label><input id="kode" name="kode" class="form-control" value="{{ request('kode') }}"></div>
                    <div class="col-md-6"><label for="nama">Nama</label><input id="nama" name="nama" class="form-control" value="{{ request('nama') }}"></div>
                </div>
                <button class="btn btn-primary mt-1">Filter</button>
                <a class="btn btn-secondary mt-1" href="{{ route('categories.index') }}">Reset</a>
            </form>
            <div class="table-responsive"><table class="table table-striped">
                <thead><tr><th>Kode</th><th>Nama</th><th>Jumlah Item</th><th>View</th></tr></thead>
                <tbody>
                @forelse($categories as $category)
                    <tr><td>{{ $category->kode }}</td><td>{{ $category->nama }}</td><td>{{ $category->items_count }}</td><td><a class="btn btn-primary" href="{{ route('categories.show', $category) }}">View</a></td></tr>
                @empty
                    <tr><td colspan="4" class="text-center">Tidak ada kategori.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div></div></div>
@endsection
