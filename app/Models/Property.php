<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 're_properties';

    protected $fillable = [
        'name',
        'price',
        'bedrooms',
        'bathrooms',
        'storeys',
        'garages',
    ];

    protected $casts = [
        'price' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'storeys' => 'integer',
        'garages' => 'integer',
    ];
}
