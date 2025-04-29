<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'image_url',
    ];

    public function getImageUrlAttribute($value): ?string
    {
        return $value ? asset("storage/products/{$value}") : null;
    }
}
