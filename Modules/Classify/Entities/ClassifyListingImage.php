<?php

namespace Modules\Classify\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassifyListingImage extends Model
{
    protected $table = 'classify_listing_images';
    protected $guarded = ['id'];

    protected $casts = [
        'listing_id' => 'integer',
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ClassifyListing::class, 'listing_id');
    }
}
