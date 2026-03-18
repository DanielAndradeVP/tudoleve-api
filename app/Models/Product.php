<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'description',
        'is_active',
        'price',
        'promotional_price',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

