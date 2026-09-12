<form method="POST" enctype="multipart/form-data">
    @csrf
    @if($method == 'edit')
    <div class="form-group">
        <label>Kode Barang</label>
        <input type="text" class="form-control" name="kode_barang" required readonly value="{{$item->kode ?? ''}}">
    </div>
    @endif

    <div class="form-group">
        <label>Nama</label>
        <input type="text" class="form-control" name="nama" required maxlength="255" value="{{ old('nama', $item->nama) }}">
    </div>

    <div class="form-group">
        <label>Harga Beli</label>
        <input type="number" class="form-control" name="harga_beli" required min="0" value="{{ old('harga_beli', $item->harga_beli) }}">
    </div>

    <div class="form-group">
        <label>Laba (dalam persen)</label>
        <input type="number" class="form-control" name="laba" required min="0" value="{{ old('laba', $item->laba) }}">
    </div>

    @php $selected = old('supplier', $item->supplier); @endphp
    <div class="form-group">
        <label>Supplier</label>
        <select class="form-control" required name="supplier">
            <option @if($selected == '') selected @endif value="">--Pilih--</option>
            <option @if($selected == 'Tokopaedi') selected @endif>Tokopaedi</option>
            <option @if($selected == 'Bukulapuk') selected @endif>Bukulapuk</option>
            <option @if($selected == 'TokoBagas') selected @endif>TokoBagas</option>
            <option @if($selected == 'E Commurz') selected @endif>E Commurz</option>
            <option @if($selected == 'Blublu') selected @endif>Blublu</option>
        </select>
    </div>

    @php $selected = old('jenis', $item->jenis); @endphp
    <div class="form-group">
        <label>Jenis</label>
        <select class="form-control" required name="jenis">
            <option @if($selected == '') selected @endif value="">--Pilih--</option>
            <option @if($selected == 'Obat') selected @endif>Obat</option>
            <option @if($selected == 'Alkes') selected @endif>Alkes</option>
            <option @if($selected == 'Matkes') selected @endif>Matkes</option>
            <option @if($selected == 'Umum') selected @endif>Umum</option>
            <option @if($selected == 'ATK') selected @endif>ATK</option>
        </select>
    </div>

    <fieldset class="form-group mt-2">
        <legend class="fs-6 mb-1">Kategori</legend>
        @php $selectedCategories = session()->hasOldInput() ? old('categories', []) : $item->categories->modelKeys(); @endphp
        <div class="border rounded p-2" style="max-height:240px; overflow-y:auto">
            @foreach($categories as $category)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="category-{{ $category->id }}" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategories))>
                    <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->nama }} ({{ $category->kode }})</label>
                </div>
            @endforeach
            @if($categories->isEmpty())<p class="text-muted mb-0">Belum ada kategori. <a href="{{ route('categories.create') }}">Tambah kategori</a>.</p>@endif
        </div>
        <small class="text-muted">Centang satu atau beberapa kategori untuk item ini.</small>
    </fieldset>
    <div class="form-group mt-2">
        <label for="foto">Foto</label>
        <input id="foto" type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
        <small class="text-muted">JPG, PNG, atau WebP; maksimal 2 MB. Kosongkan untuk mempertahankan foto saat edit.</small>
        @if($item->foto)
            <div class="mt-2"><img src="{{ route('items.photo', $item) }}" alt="Foto {{ $item->nama }}" class="img-thumbnail" style="max-height:160px"></div>
            <label><input type="checkbox" name="hapus_foto" value="1" @checked(old('hapus_foto'))> Hapus foto saat ini</label>
        @endif
    </div>
    <button class="btn btn-primary mt-3">Submit</button>

</form>
