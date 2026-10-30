<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:lokasis,nama'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        Lokasi::create($data);

        return back()->with('success', 'Lokasi "' . $data['nama'] . '" berhasil ditambahkan.');
    }

    public function destroy(Lokasi $lokasi): RedirectResponse
    {
        $lokasi->delete();

        return back()->with('success', 'Lokasi berhasil dihapus.');
    }
}
