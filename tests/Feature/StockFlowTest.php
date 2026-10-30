<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\CodeGenerator;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class StockFlowTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stock = new StockService(new CodeGenerator);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
    }

    public function test_receive_and_issue_store_tanggal_lokasi_and_user(): void
    {
        $user = $this->admin();
        $lokasi = Lokasi::create(['nama' => 'Gudang Utama']);
        $barang = Barang::create([
            'kode_barang' => 'BRG-TEST',
            'nama_barang' => 'Barang Uji',
            'tipe_pelacakan' => Barang::TRACK_QTY,
            'stok' => 0,
        ]);

        $masuk = Carbon::parse('2026-01-05 09:30:00');

        $this->stock->receive($barang, 10, [], $lokasi->id, $user->name, 'PT Supplier', 'awal', $user->id, $masuk);

        $trxMasuk = Transaksi::where('tipe', Transaksi::TIPE_MASUK)->firstOrFail();

        $this->assertSame('2026-01-05 09:30:00', $trxMasuk->tanggal->format('Y-m-d H:i:s'));
        $this->assertSame($lokasi->id, $trxMasuk->lokasi_id);
        $this->assertSame($user->id, $trxMasuk->user_id);
        $this->assertSame(10, $barang->fresh()->stok);

        $keluar = Carbon::parse('2026-01-06 14:00:00');
        $this->stock->issue($barang, 4, 'Teknisi A', $user->name, 'dipakai', BarangUnit::STATUS_DI_TEKNISI, $lokasi->id, $user->id, $keluar);

        $trxKeluar = Transaksi::where('tipe', Transaksi::TIPE_KELUAR)->firstOrFail();

        $this->assertSame('2026-01-06 14:00:00', $trxKeluar->tanggal->format('Y-m-d H:i:s'));
        $this->assertSame($lokasi->id, $trxKeluar->lokasi_id);
        $this->assertSame(6, $barang->fresh()->stok);
    }

    public function test_reversing_an_issue_restores_stock_and_keeps_audit_trail(): void
    {
        $user = $this->admin();
        $barang = Barang::create([
            'kode_barang' => 'BRG-VOID',
            'nama_barang' => 'Barang Void',
            'tipe_pelacakan' => Barang::TRACK_QTY,
            'stok' => 0,
        ]);

        $this->stock->receive($barang, 5, [], null, $user->name, null, null, $user->id);
        $this->stock->issue($barang, 2, 'Teknisi B', $user->name, null, BarangUnit::STATUS_DI_TEKNISI, null, $user->id);

        $issue = Transaksi::where('tipe', Transaksi::TIPE_KELUAR)->firstOrFail();
        $this->assertSame(3, $barang->fresh()->stok);

        $reversal = $this->stock->reverse($issue, 'Salah input', $user->id, $user->name);

        $this->assertSame(5, $barang->fresh()->stok);
        $this->assertSame(Transaksi::TIPE_PEMBATALAN, $reversal->tipe);
        $this->assertSame($issue->id, $reversal->reversal_of_id);
        $this->assertNotNull($issue->fresh()->voided_at);
        $this->assertSame('Salah input', $issue->fresh()->void_reason);
        $this->assertTrue($issue->fresh()->isVoided());
    }

    public function test_duplicate_serial_numbers_are_rejected_and_receipt_can_be_reversed(): void
    {
        $user = $this->admin();
        $barang = Barang::create([
            'kode_barang' => 'BRG-UNIT',
            'nama_barang' => 'Barang Unit',
            'tipe_pelacakan' => Barang::TRACK_UNIT,
            'stok' => 0,
        ]);

        $this->stock->receive($barang, 1, ['SN-001'], null, $user->name, null, null, $user->id);
        $this->assertSame(1, $barang->fresh()->stok);

        try {
            $this->stock->receive($barang, 1, ['SN-001'], null, $user->name, null, null, $user->id);
            $this->fail('Serial number duplikat seharusnya ditolak.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('sudah terdaftar', $e->getMessage());
        }

        $masuk = Transaksi::where('tipe', Transaksi::TIPE_MASUK)->firstOrFail();
        $this->stock->reverse($masuk, 'Batal terima', $user->id, $user->name);

        $this->assertSame(0, $barang->fresh()->stok);
        $this->assertSame(BarangUnit::STATUS_DIBATALKAN, BarangUnit::firstOrFail()->status);
    }

    public function test_soft_deleted_barang_keeps_transaction_history(): void
    {
        $user = $this->admin();
        $barang = Barang::create([
            'kode_barang' => 'BRG-HAPUS',
            'nama_barang' => 'Barang Dihapus',
            'tipe_pelacakan' => Barang::TRACK_QTY,
            'stok' => 0,
        ]);

        $this->stock->receive($barang, 3, [], null, $user->name, null, null, $user->id);
        $this->stock->issue($barang, 3, 'Teknisi C', $user->name, null, BarangUnit::STATUS_DI_TEKNISI, null, $user->id);

        $barang->delete();

        $this->assertSoftDeleted('barangs', ['id' => $barang->id]);
        $this->assertSame(2, Transaksi::where('barang_id', $barang->id)->count());
        $this->assertNotNull(Transaksi::firstOrFail()->barang);
    }
}
