<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerintahStockCountHeader;
use App\Models\DetailAssetCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Perintah Stock Count",
 *     description="API untuk mengelola perintah stock count (header + detail)"
 * )
 */
class PerintahStockCountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/perintah-stock-count",
     *     tags={"Perintah Stock Count"},
     *     summary="List perintah stock count",
     *     @OA\Response(response=200, description="List data berhasil diambil")
     * )
     */
    public function index()
    {
        $data = PerintahStockCountHeader::with(['details', 'pic', 'assetCountHeader.pic', 'details.asset', 'details.lokasi'])->orderByDesc('id')->get();

        $assetCountHeaderIds = $data->pluck('assetCountHeader.id')->filter()->unique();

        if ($assetCountHeaderIds->isNotEmpty()) {
            $assetCountDetails = DetailAssetCount::whereIn('header_asset_count_id', $assetCountHeaderIds)->get();

            $data->each(function ($header) use ($assetCountDetails) {
                if ($header->assetCountHeader) {
                    $detailsWithCount = $header->details->map(function ($detail) use ($header, $assetCountDetails) {
                        $assetCountDetail = $assetCountDetails->where('header_asset_count_id', $header->assetCountHeader->id)
                            ->where('line_perintah', $detail->LineNumber)
                            ->first();

                        $detail->JumlahHasil = $assetCountDetail->Jumlah ?? null;
                        $detail->JumlahTidakValid = $assetCountDetail->JumlahTidakValid ?? null;
                        return $detail;
                    });
                    $header->details = $detailsWithCount;
                }
            });
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * @OA\Post(
     *     path="/api/perintah-stock-count",
     *     tags={"Perintah Stock Count"},
     *     summary="Tambah data perintah stock count",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"TglPerintah", "PIC", "details"},
     *             @OA\Property(property="TglPerintah", type="string", example="2025-11-02"),
     *             @OA\Property(property="PIC", type="integer", example=5),
     *             @OA\Property(property="Keterangan", type="string", example="Stock opname gudang A"),
     *             @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/PerintahStockCountDetail")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Data berhasil disimpan")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'TglPerintah' => 'required|date',
            'PIC' => 'required|integer',
            'Keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.KodeAsset' => 'required|string',
            'details.*.KodeLokasi' => 'required|string',
            'details.*.Jumlah' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $NoTransaksi = $this->generateNoTransaksi();

            $header = PerintahStockCountHeader::create([
                'NoTransaksi' => $NoTransaksi,
                'TglPerintah' => $validated['TglPerintah'],
                'PIC' => $validated['PIC'],
                'Keterangan' => $validated['Keterangan'] ?? null,
            ]);

            foreach ($validated['details'] as $i => $detail) {
                $header->details()->create([
                    'KodeAsset' => $detail['KodeAsset'],
                    'LineNumber' => $i + 1,
                    'KodeLokasi' => $detail['KodeLokasi'],
                    'Jumlah' => $detail['Jumlah'],
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'data' => $header->load('details')
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/perintah-stock-count/{id}",
     *     tags={"Perintah Stock Count"},
     *     summary="Tampilkan detail perintah stock count",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Data ditemukan"),
     *     @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function show($id)
    {
        $header = PerintahStockCountHeader::with(['details', 'pic', 'assetCountHeader.pic'])->find($id);
        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        if ($header->assetCountHeader) {
            $assetCountDetails = DetailAssetCount::where('header_asset_count_id', $header->assetCountHeader->id)->get();

            $detailsWithCount = $header->details->map(function ($detail) use ($header, $assetCountDetails) {
                $assetCountDetail = $assetCountDetails->where('line_perintah', $detail->LineNumber)
                    ->first();

                $detail->JumlahHasil = $assetCountDetail->Jumlah ?? null;
                $detail->JumlahTidakValid = $assetCountDetail->JumlahTidakValid ?? null;
                return $detail;
            });
            $header->details = $detailsWithCount;
        }

        return response()->json(['status' => 'success', 'data' => $header]);
    }

    /**
     * @OA\Put(
     *     path="/api/perintah-stock-count/{id}",
     *     tags={"Perintah Stock Count"},
     *     summary="Update data perintah stock count",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="TglPerintah", type="string", example="2025-11-05"),
     *             @OA\Property(property="PIC", type="integer", example=2),
     *             @OA\Property(property="Keterangan", type="string", example="Revisi stok"),
     *             @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/PerintahStockCountDetail")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Data berhasil diupdate")
     * )
     */
    public function update(Request $request, $id)
    {
        $header = PerintahStockCountHeader::find($id);
        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'TglPerintah' => 'required|date',
            'PIC' => 'required|integer',
            'Keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $header->update([
                'TglPerintah' => $validated['TglPerintah'],
                'PIC' => $validated['PIC'],
                'Keterangan' => $validated['Keterangan'] ?? null,
            ]);

            // hapus detail lama & insert baru
            $header->details()->delete();
            foreach ($validated['details'] as $i => $detail) {
                $header->details()->create([
                    'KodeAsset' => $detail['KodeAsset'],
                    'LineNumber' => $i + 1,
                    'KodeLokasi' => $detail['KodeLokasi'],
                    'Jumlah' => $detail['Jumlah'],
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/perintah-stock-count/{id}",
     *     tags={"Perintah Stock Count"},
     *     summary="Hapus data perintah stock count (soft delete)",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Data berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $header = PerintahStockCountHeader::find($id);
        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $header->delete();
        return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus (soft delete)']);
    }

    private function generateNoTransaksi(): string
    {
        $prefix = 'PSC-' . now()->format('Ymd') . '-';
        $last = PerintahStockCountHeader::withTrashed()
            ->where('NoTransaksi', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $next = 1;
        if ($last) {
            $next = (int)substr($last->NoTransaksi, -3) + 1;
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
