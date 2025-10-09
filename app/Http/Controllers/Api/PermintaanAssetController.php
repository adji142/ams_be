<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermintaanAssetHeader;
use App\Models\PermintaanAssetDetail;

/**
 * @OA\Tag(
 *     name="PermintaanAsset",
 *     description="API untuk transaksi permintaan asset"
 * )
 */
class PermintaanAssetController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/permintaan-assets",
     *     summary="List semua permintaan asset",
     *     tags={"PermintaanAsset"},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar permintaan asset",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PermintaanAssetHeader"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = PermintaanAssetHeader::with(['details', 'employee'])->orderByDesc('id');
        if ($request->filled('tgl_awal')) {
            $query->whereDate('TglTransaksi', '>=', $request->tgl_awal);
        }

        if ($request->filled('tgl_akhir')) {
            $query->whereDate('TglTransaksi', '<=', $request->tgl_akhir);
        }

        if ($request->filled('status')){
            if($request->status == 'open'){
                $query->where('DocStatus', 1);
            }
        }

        return $query->get();
    }

    /**
     * @OA\Post(
     *     path="/api/permintaan-assets",
     *     summary="Buat transaksi permintaan asset baru",
     *     tags={"PermintaanAsset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"TglTransaksi","Requester"},
     *             @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-10-08"),
     *             @OA\Property(property="Requester", type="string", example="Budi Santoso"),
     *             @OA\Property(property="Keterangan", type="string", example="Permintaan laptop dan printer"),
     *             @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"KodeAsset","NamaAsset","Qty","EstimasiHarga"},
     *                     @OA\Property(property="KodeAsset", type="string", example="AST-001"),
     *                     @OA\Property(property="NamaAsset", type="string", example="Laptop Lenovo Thinkpad"),
     *                     @OA\Property(property="Qty", type="number", example=2),
     *                     @OA\Property(property="EstimasiHarga", type="number", example=12000000)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Transaksi berhasil dibuat", @OA\JsonContent(ref="#/components/schemas/PermintaanAssetHeader")),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'TglTransaksi' => 'required|date',
            'Requester' => 'required',
            'Keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.KodeAsset' => 'required|string|max:100',
            'details.*.NamaAsset' => 'required|string|max:255',
            'details.*.Qty' => 'required|numeric|min:0',
            'details.*.EstimasiHarga' => 'required|numeric|min:0',
            'details.*.QtySerahTerima' => 'nullable|numeric|min:0',
        ]);

        // Generate NoTransaksi otomatis
        $latest = PermintaanAssetHeader::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $noTrans = 'PA-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Simpan header
        $header = PermintaanAssetHeader::create([
            'NoTransaksi' => $noTrans,
            'TglTransaksi' => $data['TglTransaksi'],
            'Requester' => $data['Requester'],
            'Keterangan' => $data['Keterangan'] ?? null,
        ]);

        // Simpan detail
        $noUrut = 1;
        foreach ($data['details'] as $detail) {
            $header->details()->create([
                'NoTransaksi' => $noTrans,
                'NoUrut' => $noUrut++,
                'KodeAsset' => $detail['KodeAsset'],
                'NamaAsset' => $detail['NamaAsset'],
                'Qty' => $detail['Qty'],
                'EstimasiHarga' => $detail['EstimasiHarga'],
                'QtySerahTerima' => $detail['QtySerahTerima'] ?? 0,
            ]);
        }

        return response()->json($header->load('details'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/permintaan-assets/{id}",
     *     summary="Ambil detail transaksi permintaan asset",
     *     tags={"PermintaanAsset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Data ditemukan", @OA\JsonContent(ref="#/components/schemas/PermintaanAssetHeader")),
     *     @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function show($id)
    {
        $header = PermintaanAssetHeader::with('details')->findOrFail($id);
        return response()->json($header);
    }

    /**
     * @OA\Get(
     *     path="/api/permintaan-assets-bytrx/{noTransaksi}",
     *     summary="Ambil detail transaksi permintaan asset Berdasarkan NoTransaksi",
     *     tags={"PermintaanAsset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="noTransaksi", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Data ditemukan", @OA\JsonContent(ref="#/components/schemas/PermintaanAssetHeader")),
     *     @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function showbytrx($noTransaksi)
    {
        $data = PermintaanAssetHeader::with(['details'])
            ->where('NoTransaksi', $noTransaksi)
            ->firstOrFail();

        return response()->json($data);
    }

    /**
     * @OA\Put(
     *     path="/api/permintaan-assets/{id}",
     *     summary="Update transaksi permintaan asset",
     *     tags={"PermintaanAsset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PermintaanAssetHeader")
     *     ),
     *     @OA\Response(response=200, description="Transaksi berhasil diupdate", @OA\JsonContent(ref="#/components/schemas/PermintaanAssetHeader")),
     *     @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function update(Request $request, $id)
    {
        $header = PermintaanAssetHeader::with('details')->findOrFail($id);

        $data = $request->validate([
            'TglTransaksi' => 'required|date',
            'Requester' => 'required',
            'Keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.KodeAsset' => 'required|string|max:100',
            'details.*.NamaAsset' => 'required|string|max:255',
            'details.*.Qty' => 'required|numeric|min:0',
            'details.*.EstimasiHarga' => 'required|numeric|min:0',
            'details.*.QtySerahTerima' => 'nullable|numeric|min:0',
        ]);

        $header->update([
            'TglTransaksi' => $data['TglTransaksi'],
            'Requester' => $data['Requester'],
            'Keterangan' => $data['Keterangan'] ?? null,
        ]);

        // Hapus detail lama
        $header->details()->delete();

        // Simpan detail baru
        $noUrut = 1;
        foreach ($data['details'] as $detail) {
            $header->details()->create([
                'NoTransaksi' => $header->NoTransaksi,
                'NoUrut' => $noUrut++,
                'KodeAsset' => $detail['KodeAsset'],
                'NamaAsset' => $detail['NamaAsset'],
                'Qty' => $detail['Qty'],
                'EstimasiHarga' => $detail['EstimasiHarga'],
                'QtySerahTerima' => $detail['QtySerahTerima'] ?? 0,
            ]);
        }

        return response()->json($header->load('details'));
    }

    /**
     * @OA\Delete(
     *     path="/api/permintaan-assets/{id}",
     *     summary="Hapus transaksi permintaan asset",
     *     tags={"PermintaanAsset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil dihapus"),
     *     @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function destroy($id)
    {
        $header = PermintaanAssetHeader::findOrFail($id);
        $header->details()->delete();
        $header->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }
}
