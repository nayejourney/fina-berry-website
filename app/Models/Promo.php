<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasImage;

    protected $fillable = ['title', 'description', 'image', 'starts_on', 'ends_on', 'is_active'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** Promo yang sedang berlangsung. Promo lewat tanggal akhir otomatis tidak tampil (E-08). */
    public function scopeRunning($q)
    {
        $hariIni = now()->toDateString();

        return $q->where('is_active', true)
            ->whereDate('starts_on', '<=', $hariIni)
            ->whereDate('ends_on', '>=', $hariIni);
    }

    public function getIsRunningAttribute(): bool
    {
        return $this->is_active
            && $this->starts_on->lte(today())
            && $this->ends_on->gte(today());
    }
}
