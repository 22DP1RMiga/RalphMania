<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /**
     * Tabulā nav updated_at kolonna, tikai created_at
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity.
     *
     * Tava tabulas struktūra:
     * - user_id
     * - activity_type (varchar 50)
     * - description (text)
     * - ip_address (varchar 45)
     * - created_at
     */
    public static function log(string $activityType, ?string $description = null, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
