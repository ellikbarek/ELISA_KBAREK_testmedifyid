<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = ['harga_beli' => 'integer', 'laba' => 'integer'];

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function getHargaJualAttribute(): int
    {
        return (int) round($this->harga_beli + $this->harga_beli * $this->laba / 100);
    }
}
