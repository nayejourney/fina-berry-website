<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();                  // contoh: FB-20261013-014
            $table->string('public_token', 64)->unique();          // untuk halaman konfirmasi publik
            $table->string('idempotency_key', 64)->nullable()->unique(); // anti-duplikat checkout
            $table->string('channel', 10)->default('online');      // online | offline
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('fulfillment', 20)->default('ambil');   // ambil | antar | makan_ditempat
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total')->default(0);
            // Pembayaran dilakukan di luar sistem (offline); sistem hanya memvalidasi dan merekap.
            $table->string('payment_method', 20)->default('tunai'); // tunai | transfer | qris
            $table->string('payment_status', 20)->default('belum_bayar'); // belum_bayar | lunas
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('menunggu');     // menunggu | diproses | selesai | dibatalkan
            $table->string('cancel_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('consent_at')->nullable();           // persetujuan privasi (UU PDP)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['created_at', 'status']);
            $table->index(['channel', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products'); // menu tidak dihapus permanen
            $table->string('product_name');                    // snapshot nama
            $table->unsignedInteger('unit_price');             // snapshot harga jual
            $table->unsignedInteger('unit_cost')->default(0);  // snapshot HPP (untuk margin)
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('line_total');
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
