<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermintaanScrapHeader;
use App\Models\PermintaanScrapDetail;
use Illuminate\Support\Facades\DB;
use App\Services\AssetStockService;

/**
 * @OA\Tag(name="PermintaanScrap", description="API untuk permintaan scrap asset")
 */
class PermintaanScrapController extends Controller
{
    protected $assetStockService;

    public function __construct(AssetStockService $assetStockService)
    {
        $this->assetStockService = $assetStockService;
    }
    /**
     * @OA\Get(
     *   path="/api/permintaan-scrap",
     *   summary="Ambil semua permintaan scrap (dengan filter optional tglawal, tglakhir, status)",
     *   tags={"PermintaanScrap"},
     *   @OA\Parameter(name="tglawal", in="query", description="Tanggal awal filter", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="tglakhir", in="query", description="Tanggal akhir filter", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="status", in="query", description="Filter status dokumen (open/close/batal)", @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Sukses", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PermintaanScrapHeader")))
     * )
     */
    public function index(Request $request)
    {
        $query = PermintaanScrapHeader::with(['details', 'requester']);

        // 🔹 Filter tanggal
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $query->whereBetween('TglTransaksi', [$request->tgl_awal, $request->tgl_akhir]);
        }

        // 🔹 Filter status dokumen
        if ($request->filled('status')) {
            $statusMap = [
                'open' => 1,
                'close' => 0,
                'batal' => 99
            ];
            if (isset($statusMap[strtolower($request->status)])) {
                $query->where('DocStatus', $statusMap[strtolower($request->status)]);
            }
        }

        $data = $query->orderBy('TglTransaksi', 'desc')->get();

        // Tambah field readable
        $data->each(function ($row) {
            $row->StatusText = match ($row->DocStatus) {
                0 => 'Close',
                1 => 'Open',
                99 => 'Batal',
                default => 'Unknown'
            };
        });

        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/permintaan-scrap",
     *   summary="Tambah permintaan scrap baru",
     *   tags={"PermintaanScrap"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PermintaanScrapHeader")),
     *   @OA\Response(response=200, description="Sukses")
     * )
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validate([
                'TglTransaksi' => 'required|date',
                'Requester' => 'required|integer|exists:employees,id',
                'Keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.KodeAsset' => 'required|string',
                'details.*.NamaAsset' => 'required|string',
                'details.*.Qty' => 'required|numeric|min:1',
                'details.*.KodeLokasi' => 'required|string',
                'details.*.StatusID' => 'nullable|integer|exists:master_status_assets,id',
                'details.*.Keterangan' => 'nullable|string',
            ]);

            // generate NoSerahTerima
            $latest = PermintaanScrapHeader::withTrashed()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $noSerah = 'PS-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $header = PermintaanScrapHeader::create([
                'NoTransaksi' => $noSerah,
                'TglTransaksi' => $validated['TglTransaksi'],
                'Requester' => $validated['Requester'],
                'Keterangan' => $validated['Keterangan'] ?? null,
                'DocStatus' => 1,
            ]);

            foreach ($validated['details'] as $i => $det) {
                PermintaanScrapDetail::create([
                    'NoTransaksi' => $header->NoTransaksi,
                    'NoUrut' => $i + 1,
                    'KodeAsset' => $det['KodeAsset'],
                    'NamaAsset' => $det['NamaAsset'],
                    'Qty' => $det['Qty'],
                    'KodeLokasi' => $det['KodeLokasi'],
                    'StatusID' => $det['StatusID'] ?? null,
                    'Keterangan' => $det['Keterangan'] ?? null,
                ]);
            }

            return response()->json(['message' => 'Data berhasil disimpan', 'data' => $header->load('details')]);
        });
    }

    /**
     * @OA\Get(
     *   path="/api/permintaan-scrap/{id}",
     *   summary="Ambil detail permintaan scrap berdasarkan ID",
     *   tags={"PermintaanScrap"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Sukses", @OA\JsonContent(ref="#/components/schemas/PermintaanScrapHeader"))
     * )
     */
    public function show($id)
    {
        $data = PermintaanScrapHeader::with(['details', 'requester'])->findOrFail($id);
        $data->StatusText = match ($data->DocStatus) {
            0 => 'Close',
            1 => 'Open',
            99 => 'Batal',
            default => 'Unknown'
        };
        return response()->json($data);
    }

    /**
     * @OA\Put(
     *   path="/api/permintaan-scrap/{id}",
     *   summary="Update permintaan scrap",
     *   tags={"PermintaanScrap"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/PermintaanScrapHeader")),
     *   @OA\Response(response=200, description="Sukses")
     * )
     */
    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $header = PermintaanScrapHeader::findOrFail($id);

            $validated = $request->validate([
                'TglTransaksi' => 'required|date',
                'Requester' => 'required|integer|exists:employees,id',
                'Keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
            ]);

            $header->update([
                'TglTransaksi' => $validated['TglTransaksi'],
                'Requester' => $validated['Requester'],
                'Keterangan' => $validated['Keterangan'] ?? null,
                'Approval' => 0, // 👈 Reset approval setiap update
            ]);

            // Hapus detail lama dan insert ulang
            $header->details()->delete();

            foreach ($validated['details'] as $i => $det) {
                PermintaanScrapDetail::create([
                    'NoTransaksi' => $header->NoTransaksi,
                    'NoUrut' => $i + 1,
                    'KodeAsset' => $det['KodeAsset'],
                    'NamaAsset' => $det['NamaAsset'],
                    'Qty' => $det['Qty'],
                    'KodeLokasi' => $det['KodeLokasi'],
                    'StatusID' => $det['StatusID'] ?? null,
                    'Keterangan' => $det['Keterangan'] ?? null,
                ]);
            }

            return response()->json(['message' => 'Data berhasil diperbarui', 'data' => $header->load('details')]);
        });
    }

    /**
     * @OA\Delete(
     *   path="/api/permintaan-scrap/{id}",
     *   summary="Hapus permintaan scrap (soft delete)",
     *   tags={"PermintaanScrap"},
     *   @OA\Response(response=200, description="Berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $header = PermintaanScrapHeader::findOrFail($id);
        $header->delete();
        return response()->json(['message' => 'Data berhasil dihapus (soft delete)']);
    }

    /**
     * @OA\Patch(
     *     path="/api/permintaan-scrap/{id}/approval",
     *     tags={"PermintaanScrap"},
     *     summary="Approve atau Reject Permintaan Scrap",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="Approval", type="integer", enum={1,9}),
     *             @OA\Property(property="KeteranganApproval", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status approval diperbarui")
     * )
     */
    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'Approval' => 'required|in:1,9',
            'KeteranganApproval' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $id, $request) {
            $scrap = PermintaanScrapHeader::findOrFail($id);
            $scrap->Approval = $request->Approval;
            $scrap->KeteranganApproval = $request->KeteranganApproval;
            $scrap->ApproveBy = auth()->id(); // jika pakai auth()
            $scrap->ApproveDate = now();
            $scrap->save();

            if ($request->Approval == 1) {
                $header = PermintaanScrapHeader::with('details')->findOrFail($id);
                foreach ($header->details as $d) {
                    $this->assetStockService->removeStock(
                        $d->KodeAsset,
                        $d->KodeLokasi,
                        $d->Qty,
                        $header->NoTransaksi,
                        'Scrap Asset',
                    );
                }
            }
        });

        

        return response()->json([
            'message' => 'Status approval berhasil diperbarui'
        ]);
    }

}
