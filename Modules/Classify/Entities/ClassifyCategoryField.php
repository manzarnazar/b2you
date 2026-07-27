<?php

namespace Modules\Classify\Entities;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassifyCategoryField extends Model
{
    protected $table = 'classify_category_fields';
    protected $guarded = ['id'];

    public const TYPES = [
        'text',
        'number',
        'textarea',
        'select',
        'checkbox',
        'radio',
        'date',
        'file',
    ];

    public const OPTION_TYPES = ['select', 'checkbox', 'radio'];

    protected $casts = [
        'category_id' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'options' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ClassifyListingFieldValue::class, 'field_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function needsOptions(): bool
    {
        return in_array($this->type, self::OPTION_TYPES, true);
    }

    public function toDefinitionArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'label' => $this->label,
            'name' => $this->name,
            'type' => $this->type,
            'placeholder' => $this->placeholder,
            'default_value' => $this->default_value,
            'is_required' => (bool) $this->is_required,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'options' => $this->options ?: [],
        ];
    }
}
