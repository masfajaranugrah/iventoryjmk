<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('user')->constrained('users')->nullOnDelete();
            $table->foreignId('lokasi_sebelum_id')->nullable()->after('lokasi_id')->constrained('lokasis')->nullOnDelete();
            $table->timestamp('voided_at')->nullable()->after('catatan');
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable()->after('voided_by');
            $table->foreignId('reversal_of_id')->nullable()->after('void_reason')->constrained('transaksis')->nullOnDelete();

            $table->index('voided_at');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['lokasi_sebelum_id']);
            $table->dropForeign(['voided_by']);
            $table->dropForeign(['reversal_of_id']);
            $table->dropIndex(['voided_at']);
            $table->dropColumn(['user_id', 'lokasi_sebelum_id', 'voided_at', 'voided_by', 'void_reason', 'reversal_of_id']);
        });
    }
};
