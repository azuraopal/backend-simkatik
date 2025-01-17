<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembelianController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id',
            'unit' => 'required|integer|min:1',
            'harga_beli' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $produk = Produk::find($request->id_produk);

        $produk->stok += $request->unit;
        $produk->save();

        Pembelian::create([
            'id_produk' => $produk->id,
            'unit' => $request->unit,
            'harga_beli' => $request->harga_beli,
            'total_harga' => $request->unit * $request->harga_beli,
            'tanggal_dibeli' => now(),
        ]);

        return response()->json([
            "message" => "Pembelian berhasil ditambahkan dan stok diperbarui",
            "data" => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $stok_lama = $produk->stok;
        $stok_baru = $request->input('stok');

        $produk->update($request->all());

        if ($stok_baru > $stok_lama) {
            $selisih_stok = $stok_baru - $stok_lama;

            Pembelian::create([
                'id_produk' => $produk->id,
                'unit' => $selisih_stok,
                'harga_beli' => $produk->harga_beli,
                'total_harga' => $produk->harga_beli * $selisih_stok,
                'tanggal_dibeli' => now(),
            ]);
        }

        return response()->json($produk);
    }

    public function printPdf(Request $request)
    {
        $query = Pembelian::with('produk');

        if ($request->has('nama_produk')) {
            $query->whereHas('produk', function ($q) use ($request) {
                $q->where('nama_produk', 'like', "%{$request->nama_produk}%");
            });
        }

        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereMonth('tanggal_dibeli', $request->bulan)
                ->whereYear('tanggal_dibeli', $request->tahun);
        }

        $pembelian = $query->get();

        if ($pembelian->isEmpty()) {
            return response()->json(['message' => 'Data pembelian tidak ditemukan'], 404);
        }

        $pdf = Pdf::loadView('pembelian.pdf', compact('pembelian'));

        if ($request->query('action') === 'download') {
            return $pdf->download('laporan_pembelian.pdf');
        }

        return $pdf->stream('laporan_pembelian.pdf');
    }
}
