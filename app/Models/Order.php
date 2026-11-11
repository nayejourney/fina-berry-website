<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const CHANNEL_ONLINE = 'online';
    public const CHANNEL_OFFLINE = 'offline';

    public const STATUS_PENDING = 'menunggu';
    public const STATUS_PROCESSING = 'diproses';
    public const STATUS_DONE = 'selesai';
    public const STATUS_CANCELLED = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_PROCESSING => 'Diproses',
        self::STATUS_DONE => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public const PAYMENT_UNPAID = 'belum_bayar';
    public const PAYMENT_PAID = 'lunas';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID => 'Belum dibayar',
        self::PAYMENT_PAID => 'Lunas (tervalidasi)',
    ];

    public const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer bank',
        'qris' => 'QRIS',
    ];

    public const FULFILLMENTS = [
        'ambil' => 'Ambil di warung',
        'antar' => 'Diantar',
        'makan_ditempat' => 'Makan di tempat',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'consent_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentValidator()
    {
        return $this->belongsTo(User::class, 'paid_validated_by');
    }

    /** Pesanan yang dihitung sebagai pendapatan: berstatus Selesai dan pembayaran tervalidasi. */
    public function scopeCounted($q)
    {
        return $q->where('status', self::STATUS_DONE)->where('payment_status', self::PAYMENT_PAID);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? '-';
    }

    public function getFulfillmentLabelAttribute(): string
    {
        return self::FULFILLMENTS[$this->fulfillment] ?? '-';
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'text-bg-warning',
            self::STATUS_PROCESSING => 'text-bg-info',
            self::STATUS_DONE => 'text-bg-success',
            default => 'text-bg-secondary',
        };
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }
}
