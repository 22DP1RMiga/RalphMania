<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'video_id', 'stars'];

    // Foreign key "user_id" relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Foreign key "video_id" relationships
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
