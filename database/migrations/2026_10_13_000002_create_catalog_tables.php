<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Nilai uang disimpan sebagai bilangan bulat rupiah (tanpa desimal).
            $table->unsignedInteger('price');
            $table->unsignedInteger('cost_price')->default(0); // HPP per porsi, dipakai untuk laba/rugi
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true); // false = ditandai "habis"
            $table->boolean('is_active')->default(true);    // false = dinonaktifkan (tidak dihapus permanen)
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
