<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->foreignId('barang_unit_id')->nullable()->constrained('barang_units')->nullOnDelete();
            $table->string('tipe'); // masuk, keluar, kembali, pindah, pemeriksaan, penyesuaian
            $table->integer('jumlah')->default(1);
            $table->string('status_sebelum')->nullable();
            $table->string('status_sesudah')->nullable();
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasis')->nullOnDelete();
            $table->string('nama_pengambil')->nullable();
            $table->string('nama_pemasok')->nullable();
            $table->string('user')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal')->useCurrent();
            $table->timestamps();

            $table->index('tipe');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
