<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'contact_messages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'country_code',
        'phone',
        'subject',
        'message',
        'is_read',
        'is_replied',
        'reply_text',
        'replied_at',
        'replied_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_read' => 'boolean',
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that sent the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the administrator who replied to the message.
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Get full phone number with country code.
     */
    public function getFullPhoneAttribute(): ?string
    {
        if ($this->phone && $this->country_code) {
            return $this->country_code . ' ' . $this->phone;
        }
        return $this->phone;
    }

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read but not replied messages.
     */
    public function scopeReadNotReplied($query)
    {
        return $query->where('is_read', true)->where('is_replied', false);
    }

    /**
     * Scope for replied messages.
     */
    public function scopeReplied($query)
    {
        return $query->where('is_replied', true);
    }

    /**
     * Scope for unreplied messages.
     */
    public function scopeUnreplied($query)
    {
        return $query->where('is_replied', false);
    }
}
