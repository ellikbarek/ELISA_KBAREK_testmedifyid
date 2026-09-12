@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group mb-2">
                <a href="{{url('master-items/form/new')}}" class="btn btn-secondary">+ Master Items Baru</a>
                <a href="{{ route('items.export') }}" id="export-items" class="btn btn-success">Download Excel</a>
            </div>
            <div class="card">
                <div class="card-header">DDaftar Master Items</div>

                <div class="card-body">
                    @include('master_items.index.filter')
                    <div id="filter-error" class="alert alert-danger mt-2" role="alert" style="display:none"></div>
                    @include('master_items.index.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@include('master_items.index.js')
@endsection
