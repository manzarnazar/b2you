<?php

namespace Modules\Classify\Entities;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassifyMessage extends Model
{
    protected $table = 'classify_messages';

    protected $guarded = ['id'];

    protected $casts = [
        'conversation_id' => 'integer',
        'customer_id' => 'integer',
        'vendor_id' => 'integer',
        'is_seen' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ClassifyConversation::class, 'conversation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
