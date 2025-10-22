<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetMasterImport; // (Akan kita buat di Langkah 4)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\ValidationException;

class ImportAssetController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/master-asset/import-bulk",
     * summary="Import bulk master asset dari file Excel/CSV",
     * tags={"MasterAsset"},
     * security={{"sanctum":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(
     * property="file",
     * type="string",
     * format="binary",
     * description="File Excel (xlsx, xls, csv) yang akan diimport."
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Import berhasil",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Import bulk asset berhasil."),
     * @OA\Property(property="summary", type="object", 
     * @OA\Property(property="total_rows", type="integer", example=10),
     * @OA\Property(property="created_assets", type="integer", example=15)
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validasi data gagal",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Validasi data gagal."),
     * @OA\Property(property="errors", type="array", @OA\Items(type="object"))
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal server error"
     * )
     * )
     */
    public function importBulk(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // max 10MB
        ]);

        // Kita gunakan transaction agar jika ada 1 saja yang gagal, semua dibatalkan.
        DB::beginTransaction();

        try {
            // Ambil ID user yang sedang login untuk kolom 'created_by'
            $userId = auth()->id();
            
            $importer = new AssetMasterImport($userId);
            
            Excel::import($importer, $request->file('file'));

            DB::commit();

            // Ambil ringkasan hasil import dari class importer
            // $summary = $importer->getSummary();

            return response()->json([
                'message' => 'Import bulk asset berhasil.',
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            // Kirim balik error validasi per baris
            return response()->json([
                'message' => 'Validasi data gagal. Periksa error di setiap baris.',
                'errors' => $e->failures()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Import Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Terjadi kesalahan pada server saat import.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}