<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'title',
        'description',
        'limit',
        'cycle',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
