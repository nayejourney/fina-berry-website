<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Catat aksi penting (perubahan harga, status, penghapusan, dsb). */
    public static function record(string $action, ?Model $entity = null, array $details = []): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id' => $entity?->getKey(),
            'details' => $details ?: null,
        ]);
    }
}
