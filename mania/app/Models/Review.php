<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'stars',
    ];

    // protected $casts = [
    //     'stars' => 'integer',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // public function video()
    // {
    //     return $this->belongsTo(Video::class);
    // }
}
