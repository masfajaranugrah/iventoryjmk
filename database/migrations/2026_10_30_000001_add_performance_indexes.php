<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->index(['barang_id', 'tanggal'], 'transaksis_barang_tanggal_index');
            $table->index(['tipe', 'tanggal'], 'transaksis_tipe_tanggal_index');
            $table->index(['tipe', 'voided_at'], 'transaksis_tipe_voided_index');
            $table->index('barang_unit_id', 'transaksis_barang_unit_index');
        });

        Schema::table('barang_units', function (Blueprint $table) {
            $table->index(['barang_id', 'status'], 'barang_units_barang_status_index');
            $table->index('lokasi_id', 'barang_units_lokasi_index');
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->index('tipe_pelacakan', 'barangs_tipe_pelacakan_index');
            $table->index('nama_barang', 'barangs_nama_index');
        });

        Schema::table('opname_items', function (Blueprint $table) {
            $table->index(['opname_id', 'selisih'], 'opname_items_opname_selisih_index');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropIndex('transaksis_barang_tanggal_index');
            $table->dropIndex('transaksis_tipe_tanggal_index');
            $table->dropIndex('transaksis_tipe_voided_index');
            $table->dropIndex('transaksis_barang_unit_index');
        });

        Schema::table('barang_units', function (Blueprint $table) {
            $table->dropIndex('barang_units_barang_status_index');
            $table->dropIndex('barang_units_lokasi_index');
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->dropIndex('barangs_tipe_pelacakan_index');
            $table->dropIndex('barangs_nama_index');
        });

        Schema::table('opname_items', function (Blueprint $table) {
            $table->dropIndex('opname_items_opname_selisih_index');
        });
    }
};
