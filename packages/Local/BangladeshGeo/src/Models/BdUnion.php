<?php

namespace Local\BangladeshGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BdUnion extends Model
{
    protected $table = 'bd_unions';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected $casts = ['status' => 'boolean'];

    public function upazila(): BelongsTo
    {
        return $this->belongsTo(BdUpazila::class, 'upazila_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
