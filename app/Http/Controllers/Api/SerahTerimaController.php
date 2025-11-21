<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\AssetStockService;

use App\Models\SerahTerimaHeader;
use App\Models\SerahTerimaDetail;
use App\Models\PermintaanAssetDetail;
use App\Models\PermintaanAssetHeader;

/**
 * @OA\Tag(
 *     name="SerahTerima",
 *     description="API untuk serah terima asset (mencatat penerimaan dari permintaan)"
 * )
 */
class SerahTerimaController extends Controller
{
    protected $assetStockService;

    public function __construct(AssetStockService $assetStockService)
    {
        $this->assetStockService = $assetStockService;
    }
    /**
     * @OA\Get(
     *     path="/api/serah-terima",
     *     summary="List serah terima",
     *     tags={"SerahTerima"},
     *     @OA\Parameter(name="tgl_awal", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="tgl_akhir", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="List serah terima", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SerahTerimaHeader")))
     * )
     */
    public function index(Request $request)
    {
        $query = SerahTerimaHeader::with(['details', 'permintaan', 'penerima'])
            ->orderByDesc('id');

        if ($request->filled('tgl_awal')) {
            $query->whereDate('TglSerahTerima', '>=', $request->tgl_awal);
        }
        if ($request->filled('tgl_akhir')) {
            $query->whereDate('TglSerahTerima', '<=', $request->tgl_akhir);
        }

        $data = $query->get();
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *     path="/api/serah-terima",
     *     summary="Buat serah terima baru",
     *     tags={"SerahTerima"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"TglSerahTerima","NomorPermintaan","PenerimaID","details"},
     *             @OA\Property(property="TglSerahTerima", type="string", format="date", example="2025-10-09"),
     *             @OA\Property(property="NomorPermintaan", type="string", example="PA-20251008-0001"),
     *             @OA\Property(property="PenerimaID", type="integer", example=3),
     *             @OA\Property(property="Keterangan", type="string", example="Serah terima parsial"),
     *             @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"NoUrutPermintaan","KodeAsset","NamaAsset","QtyDiterima"},
     *                     @OA\Property(property="NoUrutPermintaan", type="integer", example=1),
     *                     @OA\Property(property="KodeAsset", type="string", example="AST-001"),
     *                     @OA\Property(property="NamaAsset", type="string", example="Laptop X"),
     *                     @OA\Property(property="QtyDiterima", type="number", format="double", example=2),
     *                     @OA\Property(property="EstimasiHarga", type="number", format="double", example=15000000),
     *                     @OA\Property(property="Keterangan", type="string", example="Good condition")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Serah terima dibuat", @OA\JsonContent(ref="#/components/schemas/SerahTerimaHeader")),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'TglSerahTerima' => 'required|date',
            'NomorPermintaan' => 'nullable|string|exists:permintaan_asset_headers,NoTransaksi',
            'PenerimaID' => 'required|integer|exists:employees,id',
            'Keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.NoUrutPermintaan' => 'required|integer',
            'details.*.KodeAsset' => 'required|string',
            'details.*.NamaAsset' => 'required|string',
            'details.*.QtyDiterima' => 'required|numeric|min:0.0',
            'details.*.EstimasiHarga' => 'nullable|numeric|min:0',
        ]);

        // Transaction
        return DB::transaction(function () use ($validated) {
            // generate NoSerahTerima
            $latest = SerahTerimaHeader::withTrashed()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $noSerah = 'STA-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // create header
            $header = SerahTerimaHeader::create([
                'NoSerahTerima' => $noSerah,
                'TglSerahTerima' => $validated['TglSerahTerima'],
                'NomorPermintaan' => $validated['NomorPermintaan'] ?? "",
                'PenerimaID' => $validated['PenerimaID'],
                'Keterangan' => $validated['Keterangan'] ?? null,
                'DocStatus' => 1,
            ]);

            // For each detail: validate against permintaan detail accumulative qty
            foreach ($validated['details'] as $d) {
                $incoming = (float)$d['QtyDiterima'];
                if (!empty($validated['NomorPermintaan'])) {
                    // find permintaan detail row
                    $permDetail = PermintaanAssetDetail::where('NoTransaksi', $validated['NomorPermintaan'])
                        ->where('NoUrut', $d['NoUrutPermintaan'])
                        ->first();

                    if (!$permDetail) {
                        throw ValidationException::withMessages([
                            'details' => ["Detail permintaan (NoUrut {$d['NoUrutPermintaan']}) tidak ditemukan."]
                        ]);
                    }

                    $currentReceived = $permDetail->QtySerahTerima ?? 0;
                    
                    $maxAllowed = (float)$permDetail->Qty;

                    if (($currentReceived + $incoming) > $maxAllowed + 1e-9) {
                        throw ValidationException::withMessages([
                            'details' => ["Qty diterima (NoUrut {$d['NoUrutPermintaan']}) melebihi sisa permintaan."]
                        ]);
                    }
                }

                // create serah terima detail
                $header->details()->create([
                    'NoSerahTerima' => $noSerah,
                    'NomorPermintaan' => $validated['NomorPermintaan'] ?? "",
                    'NoUrutPermintaan' => $d['NoUrutPermintaan'] ?? null,
                    'KodeAsset' => $d['KodeAsset'],
                    'NamaAsset' => $d['NamaAsset'],
                    'KodeLokasi' => $d['KodeLokasi'],
                    'QtyDiterima' => $d['QtyDiterima'],
                    'EstimasiHarga' => $d['EstimasiHarga'] ?? 0,
                    'Keterangan' => $d['Keterangan'] ?? null,
                ]);

                if (!empty($validated['NomorPermintaan'])) {
                    // update permintaan_asset_details QtySerahTerima (increment)
                    $permDetail->increment('QtySerahTerima', $incoming);
                }

                // update master_asset Jumlah (increment)
                $this->assetStockService->addStock(
                    $d['KodeAsset'],
                    $d['KodeLokasi'],
                    $incoming,
                    $noSerah,
                    'SerahTerimaAsset'
                );
            }

            if (!empty($validated['NomorPermintaan'])) {
                // After inserting all, check if permintaan should be closed
                $permHeader = PermintaanAssetHeader::with('details')->where('NoTransaksi', $validated['NomorPermintaan'])->first();
                $allFulfilled = $permHeader->details->every(function ($pd) {
                    return ((float)($pd->QtySerahTerima ?? 0) >= (float)$pd->Qty - 1e-9);
                });

                if ($allFulfilled) {
                    $permHeader->update(['DocStatus' => 0]); // Close
                }
            }

            return response()->json($header->load(['details', 'permintaan', 'penerima']), 201);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/serah-terima/{id}",
     *     summary="Show serah terima detail",
     *     tags={"SerahTerima"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/SerahTerimaHeader")),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show($id)
    {
        $header = SerahTerimaHeader::with(['details', 'permintaan', 'penerima','details.lokasi'])->findOrFail($id);
        return response()->json($header);
    }

    /**
     * @OA\Put(
     *     path="/api/serah-terima/{id}",
     *     summary="Update serah terima",
     *     tags={"SerahTerima"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SerahTerimaHeader")
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/SerahTerimaHeader"))
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'TglSerahTerima' => 'required|date',
            'NomorPermintaan' => 'nullable|string|exists:permintaan_asset_headers,NoTransaksi',
            'PenerimaID' => 'required|integer|exists:employees,id',
            'Keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.NoUrutPermintaan' => 'nullable|integer',
            'details.*.KodeAsset' => 'required|string',
            'details.*.NamaAsset' => 'required|string',
            'details.*.QtyDiterima' => 'required|numeric|min:0.0',
            'details.*.EstimasiHarga' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $header = SerahTerimaHeader::with('details')->findOrFail($id);

            if (!empty($header->NomorPermintaan)) {
                // Revert previous QtyDiterima from permintaan details
                foreach ($header->details as $old) {
                    $permDetail = PermintaanAssetDetail::where('NoTransaksi', $old->NomorPermintaan)
                        ->where('NoUrut', $old->NoUrutPermintaan)
                        ->first();
                    if ($permDetail) {
                        // subtract previous received
                        $permDetail->decrement('QtySerahTerima', $old->QtyDiterima);
                    }
                }
            }

            // delete old details
            $header->details()->delete();

            // update header fields
            $header->update([
                'TglSerahTerima' => $validated['TglSerahTerima'],
                'NomorPermintaan' => $validated['NomorPermintaan'] ?? "",
                'PenerimaID' => $validated['PenerimaID'],
                'Keterangan' => $validated['Keterangan'] ?? null,
            ]);

            // process new details with validation against permintaan (after revert)
            foreach ($validated['details'] as $d) {
                if (!empty($validated['NomorPermintaan'])) {
                    $permDetail = PermintaanAssetDetail::where('NoTransaksi', $validated['NomorPermintaan'])
                        ->where('NoUrut', $d['NoUrutPermintaan'])
                        ->first();

                    if (!$permDetail) {
                        throw ValidationException::withMessages([
                            'details' => ["Detail permintaan (NoUrut {$d['NoUrutPermintaan']}) tidak ditemukan."]
                        ]);
                    }

                    $currentReceived = $permDetail->QtySerahTerima ?? 0;
                    $incoming = (float)$d['QtyDiterima'];
                    $maxAllowed = (float)$permDetail->Qty;

                    if (($currentReceived + $incoming) > $maxAllowed + 1e-9) {
                        throw ValidationException::withMessages([
                            'details' => ["Qty diterima (NoUrut {$d['NoUrutPermintaan']}) melebihi sisa permintaan."]
                        ]);
                    }
                }

                // insert new detail
                $header->details()->create([
                    'NoSerahTerima' => $header->NoSerahTerima,
                    'NomorPermintaan' => $validated['NomorPermintaan'] ?? "",
                    'NoUrutPermintaan' => $d['NoUrutPermintaan'] ?? null,
                    'KodeAsset' => $d['KodeAsset'],
                    'NamaAsset' => $d['NamaAsset'],
                    'QtyDiterima' => $d['QtyDiterima'],
                    'EstimasiHarga' => $d['EstimasiHarga'] ?? 0,
                    'Keterangan' => $d['Keterangan'] ?? null,
                ]);

                if (!empty($validated['NomorPermintaan'])) {
                    $permDetail->increment('QtySerahTerima', $d['QtyDiterima']);
                }
            }

            if (!empty($validated['NomorPermintaan'])) {
                // After processing, check if permintaan should be closed
                $permHeader = PermintaanAssetHeader::with('details')->where('NoTransaksi', $validated['NomorPermintaan'])->first();
                $allFulfilled = $permHeader->details->every(function ($pd) {
                    return ((float)($pd->QtySerahTerima ?? 0) >= (float)$pd->Qty - 1e-9);
                });
                if ($allFulfilled) {
                    $permHeader->update(['DocStatus' => 0]); // Close
                } else {
                    // if not full, ensure open
                    $permHeader->update(['DocStatus' => 1]);
                }
            }

            return response()->json($header->load(['details', 'permintaan', 'penerima']));
        });
    }

    /**
     * @OA\Delete(
     *     path="/api/serah-terima/{id}",
     *     summary="Hapus serah terima",
     *     tags={"SerahTerima"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dihapus"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $header = \App\Models\SerahTerimaHeader::with('details')->findOrFail($id);

            if (!empty($header->NomorPermintaan)) {
                // ✅ Revert qty ke permintaan detail
                foreach ($header->details as $d) {
                    $permDetail = \App\Models\PermintaanAssetDetail::where('NoTransaksi', $d->NomorPermintaan)
                        ->where('NoUrut', $d->NoUrutPermintaan)
                        ->first();
                    if ($permDetail) {
                        $permDetail->decrement('QtySerahTerima', $d->QtyDiterima);
                    }

                    $this->assetStockService->removeStock(
                        $d->KodeAsset,
                        $d->KodeLokasi,
                        $d->QtyDiterima,
                        $header->NoSerahTerima,
                        'SerahTerimaAsset (Delete)'
                    );
                }
            }

            // ✅ Soft delete (akan set deleted_at, bukan hapus data)
            $header->delete();

            if (!empty($header->NomorPermintaan)) {
                // ✅ Update DocStatus permintaan (jika masih ada QtySerahTerima < Qty → tetap open)
                $permHeader = \App\Models\PermintaanAssetHeader::with('details')
                    ->where('NoTransaksi', $header->NomorPermintaan)
                    ->first();

                if ($permHeader) {
                    $allFulfilled = $permHeader->details->every(function ($pd) {
                        return ((float)($pd->QtySerahTerima ?? 0) >= (float)$pd->Qty - 1e-9);
                    });
                    $permHeader->update(['DocStatus' => $allFulfilled ? 0 : 1]);
                }
            }

            return response()->json(['message' => 'Serah terima berhasil dihapus (soft delete)']);
        });
    }

    /**
     * @OA\Post(
     *     path="/api/serah-terima/{id}/restore",
     *     summary="Restore serah terima yang dihapus",
     *     tags={"SerahTerima"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore($id)
    {
        $header = \App\Models\SerahTerimaHeader::onlyTrashed()->findOrFail($id);
        $header->restore();
        $header->details()->restore();
        return response()->json(['message' => 'Serah terima berhasil direstore']);
    }


}
