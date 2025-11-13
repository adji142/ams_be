<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermintaanScrapApproval;
use App\Models\PermintaanScrapHeader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Permintaan Scrap Approval",
 *     description="API untuk proses approval dan reject permintaan scrap"
 * )
 */
class PermintaanScrapApprovalController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/permintaan-scrap-approval/{id}/approve",
     *     tags={"Permintaan Scrap Approval"},
     *     summary="Approve permintaan scrap (menambah log approval baru)",
     *     description="Menambahkan baris approval baru dan memperbarui status header permintaan scrap",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID data approval yang sedang diproses (untuk referensi)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="NoTransaksi", type="string", example="PSC-20251107-002"),
     *             @OA\Property(property="Level", type="integer", example=2),
     *             @OA\Property(property="ApproverID", type="integer", example=8),
     *             @OA\Property(property="Keterangan", type="string", example="Disetujui oleh supervisor")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Approval berhasil disimpan dan header diperbarui"),
     *     @OA\Response(response=404, description="Data header tidak ditemukan")
     * )
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'NoTransaksi' => 'required|string|max:50',
            'Level' => 'required|integer',
            'ApproverID' => 'required|integer',
            'Keterangan' => 'nullable|string'
        ]);

        $header = PermintaanScrapHeader::where('NoTransaksi', $request->NoTransaksi)->first();
        if (!$header) {
            return response()->json(['success' => false, 'message' => 'Header tidak ditemukan'], 404);
        }

        DB::transaction(function () use ($request, $header) {
            // Insert log baru ke permintaan_scrap_approval
            $approval = PermintaanScrapApproval::create([
                'NoTransaksi' => $request->NoTransaksi,
                'Level' => $request->Level,
                'ApproverID' => $request->ApproverID,
                'Status' => 1, // approved
                'Keterangan' => $request->Keterangan ?? null,
                'ApprovedAt' => Carbon::now(),
            ]);

            // Update status terakhir ke header
            $header->Approval = $approval->Level;
            $header->KeteranganApproval = $approval->Keterangan;
            $header->ApproveDate = $approval->ApprovedAt;
            $header->ApproveBy = $approval->ApproverID;
            $header->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Approval berhasil disetujui dan disimpan sebagai log baru'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/permintaan-scrap-approval/{id}/reject",
     *     tags={"Permintaan Scrap Approval"},
     *     summary="Reject permintaan scrap (menambah log reject baru)",
     *     description="Menambahkan baris approval baru dengan status reject dan memperbarui status header permintaan scrap",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID data approval yang sedang diproses (untuk referensi)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="NoTransaksi", type="string", example="PSC-20251107-002"),
     *             @OA\Property(property="Level", type="integer", example=2),
     *             @OA\Property(property="ApproverID", type="integer", example=8),
     *             @OA\Property(property="Keterangan", type="string", example="Dokumen tidak lengkap")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reject berhasil disimpan dan header diperbarui"),
     *     @OA\Response(response=404, description="Data header tidak ditemukan")
     * )
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'NoTransaksi' => 'required|string|max:50',
            'Level' => 'required|integer',
            'ApproverID' => 'required|integer',
            'Keterangan' => 'nullable|string'
        ]);

        $header = PermintaanScrapHeader::where('NoTransaksi', $request->NoTransaksi)->first();
        if (!$header) {
            return response()->json(['success' => false, 'message' => 'Header tidak ditemukan'], 404);
        }

        DB::transaction(function () use ($request, $header) {
            // Insert log baru ke permintaan_scrap_approval
            $approval = PermintaanScrapApproval::create([
                'NoTransaksi' => $request->NoTransaksi,
                'Level' => $request->Level,
                'ApproverID' => $request->ApproverID,
                'Status' => 9, // rejected
                'Keterangan' => $request->Keterangan ?? null,
                'ApprovedAt' => Carbon::now(),
            ]);

            // Update status terakhir ke header
            if ($header->Approval > 0) {
                $header->Approval = $header->Approval - 1;
            }
            $header->KeteranganApproval = $approval->Keterangan;
            $header->ApproveDate = $approval->ApprovedAt;
            $header->ApproveBy = $approval->ApproverID;
            $header->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Approval berhasil direject dan disimpan sebagai log baru'
        ]);
    }
}
