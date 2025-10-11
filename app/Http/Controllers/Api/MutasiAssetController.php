<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MutasiAssetHeader;
use App\Models\MutasiAssetDetail;
use Illuminate\Support\Facades\DB;
use App\Services\AssetStockService;

/**
 * @OA\Tag(name="MutasiAsset", description="API untuk Mutasi Asset antar lokasi")
 */
class MutasiAssetController extends Controller
{
    protected $assetStockService;

    public function __construct(AssetStockService $assetStockService)
    {
        $this->assetStockService = $assetStockService;
    }

    /**
     * @OA\Get(
     *     path="/api/mutasi-assets",
     *     tags={"MutasiAsset"},
     *     summary="Ambil semua transaksi mutasi asset (dengan filter tanggal & status)",
     *     @OA\Response(response=200, description="Sukses")
     * )
     */
    public function index(Request $request)
    {
        $query = MutasiAssetHeader::with('details');

        if ($request->filled('tglawal') && $request->filled('tglakhir')) {
            $query->whereBetween('TglTransaksi', [$request->tglawal, $request->tglakhir]);
        }

        if ($request->filled('status')) {
            $map = ['open' => 1, 'close' => 0, 'batal' => 99];
            if (isset($map[strtolower($request->status)])) {
                $query->where('DocStatus', $map[strtolower($request->status)]);
            }
        }

        $data = $query->orderByDesc('id')->get();

        $data->each(function ($row) {
            $row->StatusText = match ($row->DocStatus) {
                1 => 'Open',
                0 => 'Close',
                99 => 'Batal',
                default => 'Unknown'
            };
        });

        return response()->json($data);
    }

    /**
     * @OA\Post(
     *     path="/api/mutasi-assets",
     *     tags={"MutasiAsset"},
     *     summary="Tambah mutasi asset baru",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/MutasiAssetHeader")),
     *     @OA\Response(response=200, description="Sukses")
     * )
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validate([
                'TglTransaksi' => 'required|date',
                'Keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.KodeAsset' => 'required|string',
                'details.*.NamaAsset' => 'required|string',
                'details.*.Qty' => 'required|numeric|min:1',
                'details.*.KodeLokasiAsal' => 'required|string',
                'details.*.KodeLokasiTujuan' => 'required|string|different:details.*.KodeLokasiAsal',
            ]);

            $latest = MutasiAssetHeader::withTrashed()->latest('id')->first();
            $nextNo = 'MT-' . now()->format('Ymd') . '-' . str_pad(($latest?->id ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            $header = MutasiAssetHeader::create([
                'NoTransaksi' => $nextNo,
                'TglTransaksi' => $validated['TglTransaksi'],
                'DocStatus' => 1,
                'Keterangan' => $validated['Keterangan'] ?? null,
            ]);

            foreach ($validated['details'] as $i => $d) {
                MutasiAssetDetail::create([
                    'NoTransaksi' => $header->NoTransaksi,
                    'NoUrut' => $i + 1,
                    'KodeAsset' => $d['KodeAsset'],
                    'NamaAsset' => $d['NamaAsset'],
                    'Qty' => $d['Qty'],
                    'KodeLokasiAsal' => $d['KodeLokasiAsal'],
                    'KodeLokasiTujuan' => $d['KodeLokasiTujuan'],
                ]);

                // 🔹 Pindahkan stok: kurangi di asal, tambah di tujuan
                $this->assetStockService->removeStock(
                    $d['KodeAsset'],
                    $d['KodeLokasiAsal'],
                    $d['Qty'],
                    $header->NoTransaksi,
                    'MutasiAsset (Keluar)'
                );

                $this->assetStockService->addStock(
                    $d['KodeAsset'],
                    $d['KodeLokasiTujuan'],
                    $d['Qty'],
                    $header->NoTransaksi,
                    'MutasiAsset (Masuk)'
                );
            }

            return response()->json([
                'message' => 'Mutasi asset berhasil disimpan',
                'data' => $header->load('details')
            ]);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/mutasi-assets/{id}",
     *     tags={"MutasiAsset"},
     *     summary="Ambil detail mutasi asset",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Sukses")
     * )
     */
    public function show($id)
    {
        $header = MutasiAssetHeader::with('details')->findOrFail($id);
        $header->StatusText = match ($header->DocStatus) {
            1 => 'Open',
            0 => 'Close',
            99 => 'Batal',
            default => 'Unknown'
        };
        return response()->json($header);
    }

    
    /**
     * @OA\Put(
     *     path="/api/mutasi-assets/{id}",
     *     tags={"MutasiAsset"},
     *     summary="Update mutasi asset dan sinkron stok ulang",
     *     @OA\Response(response=200, description="Berhasil diperbarui")
     * )
     */
    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $header = MutasiAssetHeader::with('details')->findOrFail($id);

            $validated = $request->validate([
                'TglTransaksi' => 'required|date',
                'Keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.KodeAsset' => 'required|string',
                'details.*.NamaAsset' => 'required|string',
                'details.*.Qty' => 'required|numeric|min:1',
                'details.*.KodeLokasiAsal' => 'required|string',
                'details.*.KodeLokasiTujuan' => 'required|string|different:details.*.KodeLokasiAsal',
            ]);

            // 🔹 Rollback stok lama sebelum update
            foreach ($header->details as $d) {
                $this->assetStockService->removeStock(
                    $d->KodeAsset,
                    $d->KodeLokasiTujuan,
                    $d->Qty,
                    $header->NoTransaksi,
                    'MutasiAsset (Update - Tarik dari Tujuan Lama)'
                );

                $this->assetStockService->addStock(
                    $d->KodeAsset,
                    $d->KodeLokasiAsal,
                    $d->Qty,
                    $header->NoTransaksi,
                    'MutasiAsset (Update - Kembalikan ke Asal Lama)'
                );
            }

            // 🔹 Update header
            $header->update([
                'TglTransaksi' => $validated['TglTransaksi'],
                'Keterangan' => $validated['Keterangan'] ?? null,
            ]);

            // 🔹 Hapus detail lama & simpan baru
            $header->details()->delete();

            foreach ($validated['details'] as $i => $d) {
                MutasiAssetDetail::create([
                    'NoTransaksi' => $header->NoTransaksi,
                    'NoUrut' => $i + 1,
                    'KodeAsset' => $d['KodeAsset'],
                    'NamaAsset' => $d['NamaAsset'],
                    'Qty' => $d['Qty'],
                    'KodeLokasiAsal' => $d['KodeLokasiAsal'],
                    'KodeLokasiTujuan' => $d['KodeLokasiTujuan'],
                ]);

                // 🔹 Proses stok baru
                $this->assetStockService->removeStock(
                    $d['KodeAsset'],
                    $d['KodeLokasiAsal'],
                    $d['Qty'],
                    $header->NoTransaksi,
                    'MutasiAsset (Update - Keluar Baru)'
                );

                $this->assetStockService->addStock(
                    $d['KodeAsset'],
                    $d['KodeLokasiTujuan'],
                    $d['Qty'],
                    $header->NoTransaksi,
                    'MutasiAsset (Update - Masuk Baru)'
                );
            }

            return response()->json([
                'message' => 'Mutasi asset berhasil diperbarui',
                'data' => $header->load('details')
            ]);
        });
    }

    /**
     * @OA\Delete(
     *     path="/api/mutasi-assets/{id}",
     *     tags={"MutasiAsset"},
     *     summary="Hapus mutasi asset (soft delete) dan rollback stok",
     *     @OA\Response(response=200, description="Berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $header = MutasiAssetHeader::with('details')->findOrFail($id);

            // 🔹 Rollback stok
            foreach ($header->details as $d) {
                // Kembalikan stok dari lokasi tujuan → ke asal
                $this->assetStockService->removeStock(
                    $d->KodeAsset,
                    $d->KodeLokasiTujuan,
                    $d->Qty,
                    $header->NoTransaksi,
                    'MutasiAsset (Delete - Tarik dari Tujuan)'
                );

                $this->assetStockService->addStock(
                    $d->KodeAsset,
                    $d->KodeLokasiAsal,
                    $d->Qty,
                    $header->NoTransaksi,
                    'MutasiAsset (Delete - Kembalikan ke Asal)'
                );
            }

            $header->delete();

            return response()->json(['message' => 'Mutasi asset dihapus dan stok dikembalikan']);
        });
    }
}
