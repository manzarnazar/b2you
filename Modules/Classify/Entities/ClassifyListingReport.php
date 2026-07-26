<?php

namespace Modules\Classify\Entities;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassifyListingReport extends Model
{
    protected $table = 'classify_listing_reports';
    protected $guarded = ['id'];

    protected $casts = [
        'listing_id' => 'integer',
        'user_id' => 'integer',
        'handled_by' => 'integer',
        'handled_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ClassifyListing::class, 'listing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }
}
