<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama_barang');
            $table->string('merek')->nullable()->after('kategori');
            $table->string('model')->nullable()->after('merek');
            $table->string('tipe_pelacakan')->default('jumlah')->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'merek', 'model', 'tipe_pelacakan']);
        });
    }
};
