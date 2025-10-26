<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetLocationHistory;
use App\Models\MasterAsset;
use App\Models\PermintaanPerbaikanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API for dashboard summaries"
 * )
 */
class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/dashboard/summary",
     *      summary="Get asset summary",
     *      tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data summary asset berhasil diambil."),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="total_asset", type="integer", example=100),
     *                  @OA\Property(property="asset_baik", type="integer", example=95),
     *                  @OA\Property(property="asset_repair", type="integer", example=5)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function getAssetSummary()
    {
        // Total asset keseluruhan
        $totalAsset = MasterAsset::sum('Jumlah');
        
        // Asset yang sedang dalam perbaikan (approval = 1)
        $assetRepair = PermintaanPerbaikanDetail::whereHas('header', function ($query) {
            $query->where('Approval', 1);
        })->sum('Qty');

        // Asset dengan kondisi baik
        $assetBaik = $totalAsset - $assetRepair;

        return response()->json([
            'success' => true,
            'message' => 'Data summary asset berhasil diambil.',
            'data' => [
                'total_asset' => $totalAsset,
                'asset_baik' => $assetBaik,
                'asset_repair' => $assetRepair,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/dashboard/summary-by-group",
     *      summary="Get asset summary by group",
     *      tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data summary asset per grup berhasil diambil."),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="grup_asset", type="string", example="Elektronik"),
     *                      @OA\Property(property="total_asset", type="integer", example=50)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function getSummaryByGroup()
    {
        $summary = MasterAsset::query()
            ->join('grup_assets', 'master_assets.GrupAssetID', '=', 'grup_assets.id')
            ->select('grup_assets.nama_Grup as grup_asset', DB::raw('SUM(master_assets.jumlah) as total_asset'))
            ->groupBy('grup_assets.nama_Grup')
            ->orderBy('grup_assets.nama_Grup')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data summary asset per grup berhasil diambil.',
            'data' => $summary
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/dashboard/repair-summary-by-month",
     *      summary="Get asset repair summary by month",
     *      tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data summary perbaikan asset per bulan berhasil diambil."),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="bulan", type="integer", example=10),
     *                      @OA\Property(property="total_qty", type="integer", example=15)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function getRepairSummaryByMonth()
    {
        $summary = PermintaanPerbaikanDetail::query()
            ->join('permintaanperbaikanheader', 'permintaanperbaikandetail.NoTransaksi', '=', 'permintaanperbaikanheader.NoTransaksi')
            ->whereIn('permintaanperbaikanheader.Approval', [1, 2])
            ->whereNull('permintaanperbaikanheader.deleted_at')
            ->select(
                DB::raw('MONTH(permintaanperbaikanheader.TglTransaksi) as bulan'),
                DB::raw('SUM(permintaanperbaikandetail.Qty) as total_qty')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data summary perbaikan asset per bulan berhasil diambil.',
            'data' => $summary
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/dashboard/summary-by-department",
     *      summary="Get asset summary by department",
     *      tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data summary asset per departemen berhasil diambil."),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="namadept", type="string", example="IT"),
     *                      @OA\Property(property="total_asset", type="integer", example=50)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function getAssetSummaryByDepartment()
    {
        $summary = MasterAsset::query()
            ->join('employees', 'master_assets.PIC', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.name as namadept', DB::raw('SUM(master_assets.Jumlah) as total_asset'))
            ->groupBy('departments.name')
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data summary asset per departemen berhasil diambil.',
            'data' => $summary
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/dashboard/summary-by-location",
     *      summary="Get asset summary by location",
     *      tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Data summary asset per lokasi berhasil diambil."),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="namalokasi", type="string", example="Gudang"),
     *                      @OA\Property(property="total_asset", type="integer", example=50)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      )
     * )
     */
    public function getAssetSummaryByLocation()
    {
        $summary = AssetLocationHistory::query()
            ->join('lokasi_assets', 'asset_location_histories.KodeLokasi', '=', 'lokasi_assets.kode_lokasi')
            ->select('lokasi_assets.nama_lokasi as namalokasi', DB::raw('SUM(asset_location_histories.Jumlah) as total_asset'))
            ->groupBy('lokasi_assets.nama_lokasi')
            ->havingRaw('SUM(asset_location_histories.Jumlah) > 0')
            ->orderBy('lokasi_assets.nama_lokasi')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data summary asset per lokasi berhasil diambil.',
            'data' => $summary
        ]);
    }
}
