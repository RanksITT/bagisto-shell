<?php

namespace Local\BangladeshGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BdDivision extends Model
{
    protected $table = 'bd_divisions';

    // Primary keys are upstream ids, so nothing is auto-generated.
    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected $casts = ['status' => 'boolean'];

    public function districts(): HasMany
    {
        return $this->hasMany(BdDistrict::class, 'division_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
