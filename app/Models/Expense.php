<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORIES = [
        'bahan_baku' => 'Belanja bahan baku',
        'gaji' => 'Gaji karyawan',
        'operasional' => 'Gas, listrik, air',
        'sewa' => 'Sewa tempat',
        'pemasaran' => 'Pemasaran',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = ['spent_on', 'category', 'description', 'amount', 'user_id'];

    protected function casts(): array
    {
        return [
            'spent_on' => 'date',
            'amount' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
