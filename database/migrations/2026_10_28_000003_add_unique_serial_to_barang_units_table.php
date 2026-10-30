<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_units', function (Blueprint $table) {
            $table->unique(['barang_id', 'serial_number'], 'barang_units_barang_serial_unique');
        });
    }

    public function down(): void
    {
        Schema::table('barang_units', function (Blueprint $table) {
            $table->dropUnique('barang_units_barang_serial_unique');
        });
    }
};
