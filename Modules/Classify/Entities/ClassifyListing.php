<?php

namespace Modules\Classify\Entities;

use App\Models\Category;
use App\Models\Module;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Zone;
use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ClassifyListing extends Model
{
    use SoftDeletes;

    protected $table = 'classify_listings';
    protected $guarded = ['id'];
    protected $appends = ['primary_image_full_url', 'images_full_url'];

    protected $casts = [
        'module_id' => 'integer',
        'store_id' => 'integer',
        'vendor_id' => 'integer',
        'zone_id' => 'integer',
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'price' => 'float',
        'is_negotiable' => 'boolean',
        'is_approved' => 'boolean',
        'is_premium' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'favorites_count' => 'integer',
        'chats_count' => 'integer',
        'premium_until' => 'datetime',
        'featured_until' => 'datetime',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->title)) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(6);
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ClassifyListingImage::class, 'listing_id')->orderBy('sort_order');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ClassifyListingFavorite::class, 'listing_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ClassifyListingReport::class, 'listing_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('is_approved', 1)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeOfStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOfModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNear($query, $latitude, $longitude, $radiusKm = 50)
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;
        $radiusMeters = (float) $radiusKm * 1000;

        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->selectRaw(
                'classify_listings.*, ST_Distance_Sphere(point(classify_listings.longitude, classify_listings.latitude), point(?, ?)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_Distance_Sphere(point(classify_listings.longitude, classify_listings.latitude), point(?, ?)) <= ?',
                [$lng, $lat, $radiusMeters]
            );
    }

    public function getPrimaryImageFullUrlAttribute()
    {
        $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        if (!$primary) {
            return Helpers::get_full_url('classify', null, 'public');
        }
        return Helpers::get_full_url('classify', $primary->image, $primary->storage ?? 'public');
    }

    public function getImagesFullUrlAttribute()
    {
        return $this->images->map(function ($img) {
            return Helpers::get_full_url('classify', $img->image, $img->storage ?? 'public');
        })->values()->all();
    }
}
