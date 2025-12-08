<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name_lv',
        'display_name_en',
        'description_lv',
        'description_en',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
