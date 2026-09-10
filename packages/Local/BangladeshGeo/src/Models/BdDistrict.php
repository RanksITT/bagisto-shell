<?php

namespace Local\BangladeshGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BdDistrict extends Model
{
    protected $table = 'bd_districts';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'has_city_corporation' => 'boolean',
        'lat' => 'float',
        'lon' => 'float',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(BdDivision::class, 'division_id');
    }

    public function upazilas(): HasMany
    {
        return $this->hasMany(BdUpazila::class, 'district_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
