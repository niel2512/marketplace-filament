<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Shoe extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'shoes';
    protected $fillable = [
        'name', //air jordan flying
        'slug', //domain.com/air-jordan-flying
        'thumbnail',
        'about',
        'price',
        'stock',
        'is_popular',
        'category_id',
        'brand_id',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value; //air jordan flying
        $this->attributes['slug'] = Str::slug($value); //domain.com/air-jordan-flying
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ShoePhoto::class);
    }
    public function sizes(): HasMany
    {
        return $this->hasMany(ShoeSize::class);
    }
}
