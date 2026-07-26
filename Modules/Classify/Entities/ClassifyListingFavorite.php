<?php

namespace Modules\Classify\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassifyListingFavorite extends Model
{
    protected $table = 'classify_listing_favorites';
    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'listing_id' => 'integer',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ClassifyListing::class, 'listing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
