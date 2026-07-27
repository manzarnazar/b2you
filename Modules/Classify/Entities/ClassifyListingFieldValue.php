<?php

namespace Modules\Classify\Entities;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassifyListingFieldValue extends Model
{
    protected $table = 'classify_listing_field_values';
    protected $guarded = ['id'];

    protected $casts = [
        'listing_id' => 'integer',
        'field_id' => 'integer',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ClassifyListing::class, 'listing_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ClassifyCategoryField::class, 'field_id');
    }

    public function decodedValue()
    {
        if ($this->value === null || $this->value === '') {
            return null;
        }

        $decoded = json_decode($this->value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return $this->value;
    }

    public function fileFullUrl(): ?string
    {
        if (!$this->field || $this->field->type !== 'file' || !$this->value) {
            return null;
        }

        return Helpers::get_full_url('classify/fields', $this->value, 'public');
    }
}
