<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasImage;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'cost_price',
        'image', 'is_available', 'is_active', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost_price' => 'integer',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Tampil di katalog publik (aktif, tersedia atau habis). */
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** Boleh dipesan: aktif dan tersedia. */
    public function scopeOrderable($q)
    {
        return $q->where('is_active', true)->where('is_available', true);
    }

    public function getIsOrderableAttribute(): bool
    {
        return $this->is_active && $this->is_available;
    }
}
