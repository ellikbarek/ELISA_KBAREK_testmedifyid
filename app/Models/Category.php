<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['nama', 'kode'];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(MasterItem::class);
    }
}
