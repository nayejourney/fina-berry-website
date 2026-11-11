<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Simpan pesanan online (persist-first, FR-08). Harga dan total dihitung ulang di server (NFR-07, E-04).
     * Kunci idempotensi memastikan satu klik menghasilkan satu pesanan (E-05).
     *
     * @param  array<int,int>  $qtyByProduct  [product_id => qty]
     */
    public function createOnline(array $data, array $qtyByProduct, string $idempotencyKey): Order
    {
        for ($i = 0; $i < 5; $i++) {
            try {
                return DB::transaction(function () use ($data, $qtyByProduct, $idempotencyKey) {
                    if ($existing = Order::where('idempotency_key', $idempotencyKey)->first()) {
                        return $existing;
                    }

                    [$rows, $subtotal] = $this->buildItems($qtyByProduct);

                    $order = Order::create([
                        'code' => $this->nextCode(),
                        'public_token' => Str::random(40),
                        'idempotency_key' => $idempotencyKey,
                        'channel' => Order::CHANNEL_ONLINE,
                        'customer_name' => $data['customer_name'],
                        'customer_phone' => preg_replace('/\D+/', '', $data['customer_phone']),
                        'fulfillment' => $data['fulfillment'],
                        'address' => $data['fulfillment'] === 'antar' ? ($data['address'] ?? null) : null,
                        'notes' => $data['notes'] ?? null,
                        'subtotal' => $subtotal,
                        'delivery_fee' => 0,
                        'total' => $subtotal,
                        'payment_method' => $data['payment_method'],
                        'payment_status' => Order::PAYMENT_UNPAID,
                        'status' => Order::STATUS_PENDING,
                        'consent_at' => now(),
                    ]);

                    $order->items()->createMany($rows);
                    $order->statusLogs()->create([
                        'from_status' => null,
                        'to_status' => Order::STATUS_PENDING,
                        'reason' => 'Pesanan dibuat pelanggan',
                    ]);

                    return $order;
                });
            } catch (UniqueConstraintViolationException $e) {
                if ($existing = Order::where('idempotency_key', $idempotencyKey)->first()) {
                    return $existing;
                }
                // Kode pesanan bentrok dengan transaksi lain: ulangi dengan nomor urut baru.
            }
        }

        throw new DomainException('Pesanan belum bisa disimpan. Silakan coba lagi.');
    }

    /**
     * Input pesanan di warung (FR-16): langsung berstatus Selesai dan pembayaran tervalidasi.
     *
     * @param  array<int,int>  $qtyByProduct
     */
    public function createOffline(array $qtyByProduct, User $by, string $paymentMethod, ?string $customerName, ?string $notes, string $fulfillment): Order
    {
        for ($i = 0; $i < 5; $i++) {
            try {
                return DB::transaction(function () use ($qtyByProduct, $by, $paymentMethod, $customerName, $notes, $fulfillment) {
                    [$rows, $subtotal] = $this->buildItems($qtyByProduct);

                    $order = Order::create([
                        'code' => $this->nextCode(),
                        'public_token' => Str::random(40),
                        'channel' => Order::CHANNEL_OFFLINE,
                        'customer_name' => $customerName ?: null,
                        'fulfillment' => $fulfillment,
                        'notes' => $notes ?: null,
                        'subtotal' => $subtotal,
                        'delivery_fee' => 0,
                        'total' => $subtotal,
                        'payment_method' => $paymentMethod,
                        'payment_status' => Order::PAYMENT_PAID,
                        'paid_at' => now(),
                        'paid_validated_by' => $by->id,
                        'status' => Order::STATUS_DONE,
                        'completed_at' => now(),
                        'created_by' => $by->id,
                    ]);

                    $order->items()->createMany($rows);
                    $order->statusLogs()->create([
                        'from_status' => null,
                        'to_status' => Order::STATUS_DONE,
                        'reason' => 'Input pesanan di warung',
                        'user_id' => $by->id,
                    ]);

                    return $order;
                });
            } catch (UniqueConstraintViolationException $e) {
                // Kode bentrok: ulangi.
            }
        }

        throw new DomainException('Pesanan belum bisa disimpan. Silakan coba lagi.');
    }

    /** Ubah status pesanan sesuai model status (FR-15). Melempar DomainException bila tidak valid. */
    public function changeStatus(Order $order, string $to, User $by, ?string $reason = null): void
    {
        $from = $order->status;

        if (! array_key_exists($to, Order::STATUSES)) {
            throw new DomainException('Status tidak dikenal.');
        }
        if ($from === $to) {
            throw new DomainException('Pesanan sudah berstatus '.$order->status_label.'.');
        }

        $reason = $reason !== null ? trim($reason) : null;

        $normal = [
            Order::STATUS_PENDING => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_DONE, Order::STATUS_CANCELLED],
        ];
        $correction = [
            Order::STATUS_DONE => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
        ];

        $isCorrection = false;
        if (isset($normal[$from]) && in_array($to, $normal[$from], true)) {
            // alur normal
        } elseif (isset($correction[$from]) && in_array($to, $correction[$from], true)) {
            if (! $by->isOwnerOrAbove()) {
                throw new DomainException('Hanya Pemilik yang dapat mengoreksi pesanan yang sudah selesai.');
            }
            $isCorrection = true;
        } else {
            throw new DomainException('Perpindahan status dari '.(Order::STATUSES[$from] ?? $from).' ke '.Order::STATUSES[$to].' tidak diperbolehkan.');
        }

        if (($to === Order::STATUS_CANCELLED || $isCorrection) && mb_strlen((string) $reason) < 5) {
            throw new DomainException('Alasan wajib diisi (minimal 5 karakter).');
        }
        if ($to === Order::STATUS_DONE && ! $order->is_paid) {
            throw new DomainException('Validasi pembayaran dulu sebelum menandai pesanan selesai.');
        }

        DB::transaction(function () use ($order, $from, $to, $by, $reason, $isCorrection) {
            $order->update([
                'status' => $to,
                'cancel_reason' => $to === Order::STATUS_CANCELLED ? $reason : null,
                'completed_at' => $to === Order::STATUS_DONE ? now() : null,
            ]);

            $order->statusLogs()->create([
                'from_status' => $from,
                'to_status' => $to,
                'reason' => $reason,
                'user_id' => $by->id,
            ]);

            if ($isCorrection) {
                AuditLog::record('order.correction', $order, ['from' => $from, 'to' => $to, 'reason' => $reason]);
            }
        });
    }

    /** Validasi pembayaran offline: mencatat metode dan waktu bayar untuk rekap laporan. */
    public function validatePayment(Order $order, string $method, ?string $note, User $by): void
    {
        if ($order->status === Order::STATUS_CANCELLED) {
            throw new DomainException('Pesanan yang dibatalkan tidak dapat divalidasi pembayarannya.');
        }
        if ($order->is_paid) {
            throw new DomainException('Pembayaran pesanan ini sudah divalidasi.');
        }
        if (! array_key_exists($method, Order::PAYMENT_METHODS)) {
            throw new DomainException('Metode pembayaran tidak dikenal.');
        }

        DB::transaction(function () use ($order, $method, $note, $by) {
            $order->update([
                'payment_method' => $method,
                'payment_status' => Order::PAYMENT_PAID,
                'paid_at' => now(),
                'paid_validated_by' => $by->id,
            ]);

            AuditLog::record('order.payment_validated', $order, array_filter([
                'method' => $method,
                'amount' => $order->total,
                'note' => $note,
            ]));
        });
    }

    /** Batalkan validasi pembayaran (khusus Pemilik, dan hanya jika pesanan belum Selesai). */
    public function revertPayment(Order $order, string $reason, User $by): void
    {
        if (! $order->is_paid) {
            throw new DomainException('Pembayaran belum divalidasi.');
        }
        if ($order->status === Order::STATUS_DONE) {
            throw new DomainException('Koreksi status pesanan dulu sebelum membatalkan validasi pembayaran.');
        }
        if (mb_strlen(trim($reason)) < 5) {
            throw new DomainException('Alasan wajib diisi (minimal 5 karakter).');
        }

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'payment_status' => Order::PAYMENT_UNPAID,
                'paid_at' => null,
                'paid_validated_by' => null,
            ]);

            AuditLog::record('order.payment_reverted', $order, ['reason' => trim($reason)]);
        });
    }

    /** Teks pesanan standar untuk WhatsApp (FR-09). */
    public function waMessage(Order $order): string
    {
        $order->loadMissing('items');

        $baris = [setting('wa_greeting'), '', 'Kode pesanan: '.$order->code];
        $baris[] = 'Nama: '.$order->customer_name;
        $baris[] = 'WhatsApp: '.$order->customer_phone;
        $baris[] = 'Pengambilan: '.$order->fulfillment_label.($order->fulfillment === 'antar' && $order->address ? ' ke '.$order->address : '');
        $baris[] = '';
        $baris[] = 'Pesanan:';

        foreach ($order->items as $no => $item) {
            $baris[] = ($no + 1).'. '.$item->product_name.' x'.$item->quantity.' = '.rupiah($item->line_total);
        }

        $baris[] = '';
        $baris[] = 'Total: '.rupiah($order->total);
        $baris[] = 'Rencana pembayaran: '.$order->payment_method_label.' (dibayar langsung/offline)';
        if ($order->notes) {
            $baris[] = 'Catatan: '.$order->notes;
        }
        $baris[] = '';
        $baris[] = 'Terima kasih.';

        return implode("\n", $baris);
    }

    public function waUrl(Order $order): string
    {
        return 'https://wa.me/'.wa_number(setting('wa_number')).'?text='.rawurlencode($this->waMessage($order));
    }

    /** Tautan WhatsApp admin ke pelanggan (menindaklanjuti pesanan Menunggu). */
    public function waCustomerUrl(Order $order): ?string
    {
        if (! $order->customer_phone) {
            return null;
        }

        $teks = 'Halo '.$order->customer_name.', kami dari '.setting('store_name').'. Mengenai pesanan '.$order->code.' senilai '.rupiah($order->total).'.';

        return 'https://wa.me/'.wa_number($order->customer_phone).'?text='.rawurlencode($teks);
    }

    /**
     * Ambil menu terbaru dari database, pastikan semuanya boleh dipesan, lalu bentuk baris item
     * dengan snapshot nama, harga jual, dan HPP.
     *
     * @param  array<int,int>  $qtyByProduct
     * @return array{0:array<int,array<string,mixed>>,1:int}
     */
    private function buildItems(array $qtyByProduct): array
    {
        $qtyByProduct = array_filter(array_map('intval', $qtyByProduct), fn ($q) => $q > 0);
        if (! $qtyByProduct) {
            throw new DomainException('Pilih minimal satu menu.');
        }

        $products = Product::whereIn('id', array_keys($qtyByProduct))->get()->keyBy('id');
        $rows = [];
        $subtotal = 0;

        foreach ($qtyByProduct as $id => $qty) {
            $p = $products->get($id);
            if (! $p || ! $p->is_orderable) {
                throw new DomainException('Menu "'.($p->name ?? 'tidak ditemukan').'" sedang tidak tersedia.');
            }
            if ($qty > 50) {
                throw new DomainException('Jumlah per menu maksimal 50 porsi.');
            }

            $lineTotal = $p->price * $qty;
            $subtotal += $lineTotal;
            $rows[] = [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'unit_price' => $p->price,
                'unit_cost' => $p->cost_price,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];
        }

        return [$rows, $subtotal];
    }

    /** Kode unik untuk tampilan, contoh FB-20261013-014. */
        private function nextCode(): string
    {
        $prefix = 'FB-'.now()->format('Ymd').'-';

        $terakhir = Order::where('code', 'like', $prefix.'%')->pluck('code')
            ->map(function ($kode) {
                $nomor = Str::afterLast($kode, '-');

                return ctype_digit($nomor) ? (int) $nomor : 0;
            })
            ->max() ?? 0;

        return $prefix.str_pad((string) ($terakhir + 1), 3, '0', STR_PAD_LEFT);
    }
}
