<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    /**
     * Ar modeli saistītā tabula
     */
    protected $table = 'contact_messages';

    /**
     * Atribūti, kurus var piešķirt masveidā
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
        'locale',
    ];

    /**
     * Atribūti, kas jāpielieto
     */
    protected $casts = [
        'is_read' => 'boolean',
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Iegūst lietotāju, kurš nosūtīja ziņojumu
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Iegūst administratoru, kurš atbildēja uz ziņojumu
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Iegūst pilnu tālruņa numuru ar valsts kodu
     */
    public function getFullPhoneAttribute(): ?string
    {
        if ($this->phone && $this->country_code) {
            return $this->country_code . ' ' . $this->phone;
        }
        return $this->phone;
    }

    /**
     * Tvērums nelasītiem ziņojumiem.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Aptver izlasītus, bet neatbildētus ziņojumus
     */
    public function scopeReadNotReplied($query)
    {
        return $query->where('is_read', true)->where('is_replied', false);
    }

    /**
     * Aptver atbildētos ziņojumus
     */
    public function scopeReplied($query)
    {
        return $query->where('is_replied', true);
    }

    /**
     * Tvērums neatbildētajiem ziņojumiem
     */
    public function scopeUnreplied($query)
    {
        return $query->where('is_replied', false);
    }
}
