@extends('layouts.app')
@section('content')
<div class="container"><div class="row justify-content-center"><div class="col-md-8">
    <div class="form-group mb-2"><a class="btn btn-secondary" href="{{ route('categories.index') }}">Kembali ke Daftar Kategori</a></div>
    <div class="card">
        <div class="card-header">{{ $category->exists ? 'Edit Kategori Items' : 'Buat Kategori Items Baru' }}</div>
        <div class="card-body">
            <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
                @csrf
                @if($category->exists) @method('PUT') @endif
                <div class="form-group"><label for="kode">Kode</label><input id="kode" name="kode" class="form-control" required maxlength="255" value="{{ old('kode', $category->kode) }}"></div>
                <div class="form-group"><label for="nama">Nama</label><input id="nama" name="nama" class="form-control" required maxlength="255" value="{{ old('nama', $category->nama) }}"></div>
                <button class="btn btn-primary mt-3">Submit</button>
            </form>
        </div>
    </div>
</div></div></div>
@endsection
