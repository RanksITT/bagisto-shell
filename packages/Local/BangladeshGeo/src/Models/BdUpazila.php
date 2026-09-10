<?php

namespace Local\BangladeshGeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Level 3. Holds both rural upazilas (upstream) and city-corporation thanas (curated),
 * distinguished by `type`. Keeping them in one table keeps a single code path through
 * the API, validation, the observer and every display surface.
 */
class BdUpazila extends Model
{
    public const TYPE_UPAZILA = 'upazila';

    public const TYPE_THANA = 'thana';

    protected $table = 'bd_upazilas';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'unions_count' => 'integer',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(BdDistrict::class, 'district_id');
    }

    public function unions(): HasMany
    {
        return $this->hasMany(BdUnion::class, 'upazila_id');
    }

    /**
     * True when level 4 should be a union dropdown. False for metro thanas and for the
     * five rural upazilas that genuinely have no unions - both fall through to a
     * free-text area field, with no special-casing at the call site.
     */
    public function hasUnions(): bool
    {
        return $this->unions_count > 0;
    }

    public function isThana(): bool
    {
        return $this->type === self::TYPE_THANA;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
