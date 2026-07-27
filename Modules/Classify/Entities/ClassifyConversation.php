<?php

namespace Modules\Classify\Entities;

use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassifyConversation extends Model
{
    protected $table = 'classify_conversations';

    protected $guarded = ['id'];

    protected $casts = [
        'listing_id' => 'integer',
        'module_id' => 'integer',
        'store_id' => 'integer',
        'vendor_id' => 'integer',
        'customer_id' => 'integer',
        'last_message_id' => 'integer',
        'unread_customer' => 'integer',
        'unread_vendor' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ClassifyListing::class, 'listing_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClassifyMessage::class, 'conversation_id');
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(ClassifyMessage::class, 'last_message_id');
    }
}
