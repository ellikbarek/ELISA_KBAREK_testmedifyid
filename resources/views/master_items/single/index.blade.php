@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group mb-2">
                <a href="{{url('master-items')}}" class="btn btn-secondary">Kembali ke Daftar Item</a>
            </div>
            <div class="card">
                <div class="card-header">Master Item</div>

                <div class="card-body">
                    <table>
                        <tr>
                            <th>Nama</th>
                            <td>:</td>
                            <td>{{$data->nama}}</td>
                        </tr>
                        <tr>
                            <th>Harga Beli</th>
                            <td>:</td>
                            <td>{{$data->harga_beli}}</td>
                        </tr>
                        <tr>
                            <th>Laba</th>
                            <td>:</td>
                            <td>{{$data->laba}}</td>
                        </tr>
                        <tr>
                            <th>Harga Jual</th>
                            <td>:</td>
                            <td>{{$data->harga_jual}}</td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>:</td>
                            <td>{{$data->supplier}}</td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td>:</td>
                            <td>{{$data->jenis}}</td>
                        </tr>
                        <tr><th>Kategori</th><td>:</td><td>
                            @forelse($data->categories as $category)
                                <a href="{{ route('categories.show', $category) }}">{{ $category->nama }}</a>{{ !$loop->last ? ', ' : '' }}
                            @empty
                                Belum ada kategori
                            @endforelse
                        </td></tr>
                        <tr><th>Foto</th><td>:</td><td>
                            @if($data->foto)
                                <img src="{{ route('items.photo', $data) }}" alt="Foto {{ $data->nama }}" class="img-thumbnail" style="max-height:240px">
                            @else
                                Belum ada foto
                            @endif
                        </td></tr>
                    </table>
                    <a class="btn btn-info" href="{{url('master-items/form/edit')}}/{{$data->id}}">Edit</a>
                    <form class="d-inline" method="POST" action="{{ route('items.destroy', $data->id) }}" onsubmit="return confirm('Hapus item ini?');">
                        @csrf @method('DELETE')<button class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@endsection
