<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_id')->constrained('opnames')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->integer('stok_sistem')->default(0);
            $table->integer('stok_fisik')->nullable();
            $table->integer('selisih')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['opname_id', 'barang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_items');
    }
};
