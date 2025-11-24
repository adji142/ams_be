<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeaderAssetCount;
use App\Models\DetailAssetCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PerintahStockCountHeader;
use App\Models\PerintahStockCountDetail;


/**
 * @OA\Tag(
 *     name="Asset Count",
 *     description="API untuk transaksi Asset Count"
 * )
 */
class AssetCountController extends Controller
{

    public function indexPending($userId)
    {
        // Cara 1: pakai join (efisien jika tabel besar)
        $data = PerintahStockCountHeader::select('perintah_stock_count_headers.*')
            ->join('users', 'perintah_stock_count_headers.PIC', '=', 'users.KaryawanID')
            ->where('users.id', $userId)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('header_asset_counts')
                      ->whereRaw('header_asset_counts.perintah_id = perintah_stock_count_headers.id');
            })
            ->with('details', 'pic', 'details.asset', 'details.lokasi')
            ->orderByDesc('perintah_stock_count_headers.id')
            ->get();

        foreach ($data as $header) {
            foreach ($header->details as $detail) {
                $stock = \App\Models\AssetLocationHistory::where('KodeAsset', $detail->KodeAsset)
                    ->where('KodeLokasi', $detail->KodeLokasi)
                    ->first();
                $detail->stock = $stock ? $stock->Jumlah : 0;
            }
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function start($perintahId)
    {
        // Ambil data perintah + detail
        $perintah = PerintahStockCountHeader::with('details', 'pic','details.asset', 'details.lokasi','details.asset')->findOrFail($perintahId);

        return response()->json([
            'status' => 'success',
            'message' => 'Data perintah berhasil dimuat',
            'data' => $perintah,
        ]);
    }


    /**
     * @OA\Get(
     * path="/api/asset-counts",
     * tags={"Asset Count"},
     * summary="Menampilkan daftar histori asset count (header) dengan filter",
     * @OA\Parameter(
     * name="TglAwal",
     * in="query",
     * required=true,
     * description="Tanggal awal filter (format: YYYY-MM-DD)",
     * @OA\Schema(type="string", format="date")
     * ),
     * @OA\Parameter(
     * name="TglAkhir",
     * in="query",
     * required=true,
     * description="Tanggal akhir filter (format: YYYY-MM-DD)",
     * @OA\Schema(type="string", format="date")
     * ),
     * @OA\Parameter(
     * name="LokasiID",
     * in="query",
     * required=false,
     * description="Filter berdasarkan ID Lokasi",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="AssetGrupID",
     * in="query",
     * required=false,
     * description="Filter berdasarkan ID Grup Aset yang ada di dalam transaksi",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Operasi berhasil",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/HeaderAssetCount"))
     * ),
     * @OA\Response(response=422, description="Validasi gagal, parameter tanggal wajib diisi")
     * )
     */
    public function index(Request $request)
    {

        // 2. Membangun Query Dasar
        $query = HeaderAssetCount::query();

        // 3. Menerapkan Filter
        
        // Filter Wajib: Rentang Tanggal
        $query->whereBetween('TglTransaksi', [$request->TglAwal, $request->TglAkhir]);

        // Filter Opsional: Lokasi
        if ($request->filled('LokasiID')) {
            if($request->LokasiID > 0){
                $query->where('LokasiID', $request->LokasiID);
            }
        }
        
        // 4. Eager Loading dan Eksekusi Query
        $history = $query->with(['pic', 'lokasi', 'details.asset'])->get();

        return response()->json($history);
    }

    /**
     * @OA\Post(
     * path="/api/asset-counts",
     * tags={"Asset Count"},
     * summary="Membuat transaksi asset count baru (Add)",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"NoTransaksi", "TglTransaksi", "PICID", "LokasiID", "details"},
     * @OA\Property(property="NoTransaksi", type="string", example="AC/2025/10/002"),
     * @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-10-18"),
     * @OA\Property(property="PICID", type="integer", example=1),
     * @OA\Property(property="LokasiID", type="integer", example=1),
     * @OA\Property(
     * property="details",
     * type="array",
     * @OA\Items(
     * required={"AssetID", "LineNumber", "Jumlah"},
     * @OA\Property(property="AssetID", type="integer", example=101),
     * @OA\Property(property="LineNumber", type="integer", example=1),
     * @OA\Property(property="Jumlah", type="integer", example=20)
     * )
     * )
     * )
     * ),
     * @OA\Response(response=201, description="Data berhasil dibuat", @OA\JsonContent(ref="#/components/schemas/HeaderAssetCount")),
     * @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'TglTransaksi' => 'required|date',
            'PICID' => 'required|integer|exists:employees,id', // pastikan tabel 'employees' ada
            'LokasiID' => 'required|integer|exists:lokasi_assets,id', // pastikan tabel 'lokasi_assets' ada
            'perintah_id' => 'nullable|integer|exists:perintah_stock_count_headers,id',
            'JamMulai' => 'nullable',
            'JamSelesai' => 'nullable',
            'details' => 'required|array|min:1',
            'details.*.AssetID' => 'required|integer|exists:master_assets,id', // pastikan tabel 'master_assets' ada
            'details.*.LineNumber' => 'required|integer',
            'details.*.Jumlah' => 'required|integer|min:0',
            'details.*.JumlahTidakValid' => 'nullable|integer|min:0',
        ]);

        

        $header = null;
        // Generate NoTransaksi otomatis
        $latest = HeaderAssetCount::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $noTrans = 'AC-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // $header = HeaderAssetCount::create($request->only(['NoTransaksi', 'TglTransaksi', 'PICID', 'LokasiID']));
        $header = HeaderAssetCount::create([
            'NoTransaksi' => $noTrans,
            'TglTransaksi' => $data['TglTransaksi'],
            'PICID' => $data['PICID'],
            'LokasiID' => $data['LokasiID'] ?? null,
            'perintah_id' => $data['perintah_id'] ?? null,
            'JamMulai' => $data['JamMulai'] ?? null,
            'JamSelesai' => $data['JamSelesai'] ?? null,
        ]);
        foreach ($request->details as $detail) {
            $header->details()->create([
                'DetailLokasiID' => $detail['DetailLokasiID'],
                'line_perintah' => $detail['line_perintah'],
                'kode_asset_perintah' => $detail['kode_asset_perintah'],
                'AssetID' => $detail['AssetID'],
                'LineNumber' => $detail['LineNumber'],
                'Jumlah' => $detail['Jumlah'],
                'JumlahTidakValid' => $detail['JumlahTidakValid'] ?? 0,
            ]);
        }
        
        return response()->json($header->load('details'), 200);
    }

    /**
     * @OA\Get(
     * path="/api/asset-counts/{id}",
     * tags={"Asset Count"},
     * summary="Menampilkan detail sebuah transaksi asset count",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Operasi berhasil", @OA\JsonContent(ref="#/components/schemas/HeaderAssetCount")),
     * @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function show(HeaderAssetCount $headerAssetCount)
    {
        // Show detail dengan relasinya
        return response()->json($headerAssetCount->load(['details.asset', 'pic', 'lokasi']));
    }

    /**
     * @OA\Put(
     * path="/api/asset-counts/{id}",
     * tags={"Asset Count"},
     * summary="Mengupdate transaksi asset count (header dan semua detail sekaligus)",
     * description="Endpoint ini menangani update header, penambahan detail baru, update detail yang ada, dan penghapusan detail dalam satu panggilan API.",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"TglTransaksi", "PICID", "LokasiID", "details"},
     * @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-10-18"),
     * @OA\Property(property="PICID", type="integer", example=2),
     * @OA\Property(property="LokasiID", type="integer", example=3),
     * @OA\Property(
     * property="details",
     * type="array",
     * description="Array lengkap dari semua detail. Kirim 'id' untuk item yang sudah ada, jangan kirim 'id' untuk item baru.",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", description="ID detail (opsional, hanya untuk update)", example=1),
     * @OA\Property(property="AssetID", type="integer", example=101),
     * @OA\Property(property="LineNumber", type="integer", example=1),
     * @OA\Property(property="Jumlah", type="integer", example=99)
     * )
     * )
     * )
     * ),
     * @OA\Response(response=200, description="Transaksi berhasil diupdate", @OA\JsonContent(ref="#/components/schemas/HeaderAssetCount")),
     * @OA\Response(response=422, description="Validasi gagal"),
     * @OA\Response(response=404, description="Data tidak ditemukan")
     * )
     */
    public function update(Request $request, HeaderAssetCount $headerAssetCount)
    {
        $validator = Validator::make($request->all(), [
            'TglTransaksi' => 'required|date',
            'PICID' => 'required|integer|exists:employees,id',
            'LokasiID' => 'required|integer|exists:lokasi_assets,id',
            'details' => 'present|array', // 'present' berarti field harus ada, meski kosong
            'details.*.id' => 'sometimes|integer|exists:detail_asset_counts,id', // 'sometimes' berarti field boleh tidak ada (untuk item baru)
            'details.*.AssetID' => 'required|integer|exists:master_assets,id',
            'details.*.LineNumber' => 'required|integer',
            'details.*.Jumlah' => 'required|integer|min:0',
            'details.*.JumlahTidakValid' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            // 1. Update Header
            $headerAssetCount->update($request->only(['TglTransaksi', 'PICID', 'LokasiID']));

            $incomingDetailIds = [];

            // 2. Proses Create atau Update Detail
            foreach ($request->details as $detailData) {
                // Gunakan updateOrCreate untuk efisiensi
                // Jika ada 'id', ia akan update. Jika tidak ada 'id', ia akan create.
                $detail = DetailAssetCount::updateOrCreate(
                    [
                        'id' => $detailData['id'] ?? null, // Cari berdasarkan ID jika ada
                        'header_asset_count_id' => $headerAssetCount->id
                    ],
                    [
                        'AssetID' => $detailData['AssetID'],
                        'LineNumber' => $detailData['LineNumber'],
                        'Jumlah' => $detailData['Jumlah'],
                        'JumlahTidakValid' => $detailData['JumlahTidakValid'] ?? 0,
                    ]
                );
                
                // Kumpulkan ID dari detail yang diproses
                $incomingDetailIds[] = $detail->id;
            }

            // 3. Hapus Detail yang tidak ada di request
            // Ini akan menghapus detail yang dihapus oleh user di frontend
            DetailAssetCount::where('header_asset_count_id', $headerAssetCount->id)
                ->whereNotIn('id', $incomingDetailIds)
                ->delete();

            DB::commit();

            // Kembalikan data terbaru setelah semua proses selesai
            return response()->json($headerAssetCount->load('details'));

        } catch (\Exception $e) {
            DB::rollBack();
            // Berikan response error yang informatif
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}