<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'dev_id',
        'name',
        'date',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
