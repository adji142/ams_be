<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermintaanPerbaikanHeader;
use App\Models\PermintaanPerbaikanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="PermintaanPerbaikanAsset", description="API untuk permintaan perbaikan asset")
 */
class PermintaanPerbaikanController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/permintaan-perbaikan-assets",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Menampilkan semua permintaan perbaikan asset",
     *   @OA\Response(response=200, description="Daftar permintaan perbaikan")
     * )
     */
    public function index(Request $request)
    {
        $query = PermintaanPerbaikanHeader::with('details');

        // filter tanggal
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $query->whereBetween('TglTransaksi', [$request->tgl_awal, $request->tgl_akhir]);
        }

        // filter status
        if ($request->filled('status')) {
            $query->where('DocStatus', $request->status);
        }

        $data = $query->orderByDesc('id')->get();

        $data->each(function ($row) {
            $row->StatusText = match ($row->DocStatus) {
            "0" => 'Close',
            "1" => 'Open',
            "99" => 'Batal',
            default => 'Unknown'
            };
            
            $row->ApprovalText = match ($row->Approval) {
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
            9 => 'Canceled',
            default => 'Unknown'
            };
        });

        return response()->json($data);
    }

    /**
     * @OA\Get(
     *   path="/api/permintaan-perbaikan-assets/{id}",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Menampilkan detail perbaikan asset",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\Response(response=200, description="Detail perbaikan asset")
     * )
     */
    public function show($id)
    {
        $data = PermintaanPerbaikanHeader::with('details')->findOrFail($id);
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/permintaan-perbaikan-assets",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Menambahkan permintaan perbaikan asset",
     *   @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/PermintaanPerbaikanHeader")),
     *   @OA\Response(response=200, description="Berhasil disimpan")
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
                'details.*.KodeLokasi' => 'required|string',
            ]);

            $latest = PermintaanPerbaikanHeader::withTrashed()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $noTrans = 'PPB-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $header = PermintaanPerbaikanHeader::create([
                'NoTransaksi' => $noTrans,
                'TglTransaksi' => $validated['TglTransaksi'],
                'Keterangan' => $validated['Keterangan'] ?? null,
                'DocStatus' => 1,
                'Approval' => 0,
            ]);

            foreach ($validated['details'] as $i => $det) {
                PermintaanPerbaikanDetail::create([
                    'NoTransaksi' => $header->NoTransaksi,
                    'NoUrut' => $i + 1,
                    'KodeAsset' => $det['KodeAsset'],
                    'NamaAsset' => $det['NamaAsset'],
                    'Qty' => $det['Qty'],
                    'KodeLokasi' => $det['KodeLokasi'],
                ]);
            }

            return response()->json(['message' => 'Data berhasil disimpan', 'data' => $header->load('details')]);
        });
    }

    /**
     * @OA\Put(
     *   path="/api/permintaan-perbaikan-assets/{id}",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Update data perbaikan asset",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/PermintaanPerbaikanHeader")),
     *   @OA\Response(response=200, description="Berhasil diupdate")
     * )
     */
    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $header = PermintaanPerbaikanHeader::findOrFail($id);

            $validated = $request->validate([
                'TglTransaksi' => 'required|date',
                'Keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
            ]);

            $header->update([
                'TglTransaksi' => $validated['TglTransaksi'],
                'Keterangan' => $validated['Keterangan'] ?? '',
                'Approval' => 0, // reset approval saat update
            ]);

            $header->details()->delete();
            foreach ($validated['details'] as $i => $det) {
                PermintaanPerbaikanDetail::create([
                    'NoTransaksi' => $header->NoTransaksi,
                    'NoUrut' => $i + 1,
                    'KodeAsset' => $det['KodeAsset'],
                    'NamaAsset' => $det['NamaAsset'],
                    'Qty' => $det['Qty'],
                    'KodeLokasi' => $det['KodeLokasi'],
                ]);
            }

            return response()->json(['message' => 'Data berhasil diperbarui', 'data' => $header->load('details')]);
        });
    }

    /**
     * @OA\Delete(
     *   path="/api/permintaan-perbaikan-assets/{id}",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Soft delete permintaan perbaikan",
     *   @OA\Response(response=200, description="Berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $header = PermintaanPerbaikanHeader::findOrFail($id);
        $header->delete();
        return response()->json(['message' => 'Data berhasil dihapus (soft delete)']);
    }

    /**
     * @OA\Patch(
     *   path="/api/permintaan-perbaikan-assets/{id}/approval",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Approve/Reject permintaan perbaikan",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\RequestBody(@OA\JsonContent(
     *       @OA\Property(property="Approval", type="integer", enum={1,2,9}),
     *       @OA\Property(property="KeteranganApproval", type="string")
     *   )),
     *   @OA\Response(response=200, description="Approval berhasil diupdate")
     * )
     */
    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'Approval' => 'required|in:1,2,9',
            'KeteranganApproval' => 'nullable|string',
        ]);

        $scrap = PermintaanPerbaikanHeader::findOrFail($id);
        $scrap->Approval = $validated['Approval'];
        $scrap->KeteranganApproval = $validated['KeteranganApproval'] ?? '';
        $scrap->ApproveBy = auth()->user()->name ?? 'system';
        $scrap->ApproveDate = now();
        $scrap->save();

        return response()->json(['message' => 'Status approval berhasil diperbarui', 'data' => $scrap]);
    }

    /**
     * @OA\Get(
     *   path="/api/permintaan-perbaikan-assets/approved/{KodeAsset}",
     *   tags={"PermintaanPerbaikanAsset"},
     *   summary="Menampilkan total kuantitas aset yang sudah di approve untuk perbaikan berdasarkan KodeAsset",
     *   @OA\Parameter(name="KodeAsset", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200, 
     *     description="Total kuantitas aset yang sedang diperbaiki",
     *     @OA\JsonContent(
     *       @OA\Property(property="total_qty", type="integer", example=5)
     *     )
     *   ),
     *   @OA\Response(response=404, description="Data not found")
     * )
     */
    public function getApprovedRequests($KodeAsset)
    {
        // 1. Dapatkan total kuantitas (SUM(Qty)) dari PermintaanPerbaikanDetail
        //    yang terhubung ke header yang sudah di-approve (Approval = 1).
        
        $totalQty = PermintaanPerbaikanDetail::where('KodeAsset', $KodeAsset)
            ->whereHas('header', function ($query) {
                $query->where('Approval', 1); // 1 = Approved
            })
            ->sum('Qty'); // Menggunakan fungsi sum() untuk mengambil langsung totalnya

        // 2. Periksa apakah hasilnya 0 atau null (data tidak ditemukan)
        if (is_null($totalQty) || $totalQty === 0) {
            // Jika total kuantitas 0, kita kembalikan respons 404 (atau 200 dengan 0)
            // Saya sarankan mengembalikan 404 jika tidak ada, atau 200 dengan nilai 0
            // Sesuai logika awal Anda:
            // return response()->json(['message' => 'Data not found'], 404);

            // Namun, jika tujuannya adalah menghitung stok perbaikan,
            // mengembalikan total 0 lebih informatif daripada 404.
            return response()->json(['total_qty' => 0]);
        }

        // 3. Kembalikan total kuantitas
        // Karena kueri di kode React Anda hanya menghitung jumlah item di array respons,
        // kita ubah formatnya agar mengembalikan satu objek/nilai yang sudah di-sum.
        // Jika Anda ingin mengembalikan array seperti kode sebelumnya:
        // return response()->json([['total_qty' => (int)$totalQty]]);
        //
        // Namun, jika tujuan akhirnya hanya nilai, kembalikan nilai tunggal:
        
        return response()->json(['total_qty' => (int)$totalQty]);
    }
}
