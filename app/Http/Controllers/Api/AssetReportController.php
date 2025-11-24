<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterAsset; // Sesuaikan dengan path model Anda
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 * name="Reports",
 * description="API for generating reports"
 * )
 */
class AssetReportController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/reports/assets",
     * summary="Get a filtered list of assets for reporting",
     * tags={"Reports"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="kondisi",
     * in="query",
     * description="Filter by asset condition ('Baik' or 'Repair')",
     * required=false,
     * @OA\Schema(type="string", enum={"Baik", "Repair"})
     * ),
     * @OA\Parameter(
     * name="status_id",
     * in="query",
     * description="Filter by asset status ID",
     * required=false,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="department_id",
     * in="query",
     * description="Filter by department ID",
     * required=false,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="lokasi_id",
     * in="query",
     * description="Filter by asset location ID",
     * required=false,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * @OA\Property(property="NoAsset", type="string", example="AST-001"),
     * @OA\Property(property="NamaAsset", type="string", example="Laptop Dell"),
     * @OA\Property(property="PerkiraanHarga", type="number", format="float", example=15000000),
     * @OA\Property(property="Jumlah", type="integer", example=1),
     * @OA\Property(property="PIC", type="string", example="John Doe"),
     * @OA\Property(property="Departemen", type="string", example="IT"),
     * @OA\Property(property="Lokasi", type="string", example="Gudang Utama"),
     * @OA\Property(property="StatusAsset", type="string", example="Aktif")
     * )
     * )
     * )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'kondisi'   => 'nullable|string|in:Baik,Repair,Ex-Repair,Scrap',
            'lokasi_id' => 'nullable|integer|exists:lokasi_assets,id',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $latestLocationSubquery = DB::table('asset_location_histories')
            ->select('KodeAsset', DB::raw('MAX(id) as last_id'))
            // ->whereNull('deleted_at')
            ->groupBy('KodeAsset');

        $latestPriceSubquery = DB::table('serah_terima_details')
            ->select('KodeAsset', DB::raw('MAX(id) as last_price_id'))
            // ->whereNull('deleted_at')
            ->groupBy('KodeAsset');

        $latestStockOpnameSubquery = DB::table('detail_asset_counts as detail')
            ->select('detail.AssetID', DB::raw('MAX(header.TglTransaksi) as latest_so_date'))
            ->join('header_asset_counts as header', 'detail.header_asset_count_id', '=', 'header.id')
            // ->whereNull('detail.deleted_at')
            // ->whereNull('header.deleted_at')
            ->groupBy('detail.AssetID');

        $query = MasterAsset::query()
            ->select([
                'master_assets.*',
                'pic.name as pic_name',
                'dept.name as department_name',
                'stat.NamaStatusAsset as status_name',
                'loc.nama_lokasi as location_name',
                'std.EstimasiHarga as last_price',
                'latest_so.latest_so_date'
            ])
            ->leftJoin('employees as pic', 'master_assets.PIC', '=', 'pic.id')
            ->leftJoin('departments as dept', 'pic.department_id', '=', 'dept.id')
            ->leftJoin('master_status_assets as stat', 'master_assets.StatusID', '=', 'stat.id')
            ->leftJoinSub($latestLocationSubquery, 'latest_loc_history', function ($join) {
                $join->on('master_assets.KodeAsset', '=', 'latest_loc_history.KodeAsset');
            })
            ->leftJoin('asset_location_histories as alh', 'latest_loc_history.last_id', '=', 'alh.id')
            ->leftJoin('lokasi_assets as loc', 'alh.KodeLokasi', '=', 'loc.id')
            ->leftJoinSub($latestPriceSubquery, 'latest_price_sub', function ($join) {
                $join->on('master_assets.KodeAsset', '=', 'latest_price_sub.KodeAsset');
            })
            ->leftJoin('serah_terima_details as std', 'latest_price_sub.last_price_id', '=', 'std.id')
            ->leftJoinSub($latestStockOpnameSubquery, 'latest_so', function ($join) {
                $join->on('master_assets.id', '=', 'latest_so.AssetID');
            });

        // Filter kondisi: Repair / Baik
        $query->when($request->filled('kondisi'), function ($q) use ($request) {
            $subquery = function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('permintaanperbaikandetail as detail')
                    ->join('permintaanperbaikanheader as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi')
                    ->whereColumn('detail.KodeAsset', 'master_assets.KodeAsset')
                    // ->whereNull('detail.deleted_at')
                    ->where('header.Approval', 1);
            };

            $subexRepair = function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('permintaanperbaikandetail as detail')
                    ->join('permintaanperbaikanheader as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi')
                    ->whereColumn('detail.KodeAsset', 'master_assets.KodeAsset')
                    // ->whereNull('detail.deleted_at')
                    ->where('header.Approval', "2");
            };

            $subScap = function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('permintaan_scrap_details as detail')
                    ->join('permintaan_scrap_headers as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi')
                    ->whereColumn('detail.KodeAsset', 'master_assets.KodeAsset')
                    // ->whereNull('detail.deleted_at')
                    ->where('header.Approval', "8");
            };

            if ($request->kondisi === 'Repair') {
                return $q->whereExists($subquery);
            }

            if ($request->kondisi === 'Baik') {
                return $q->whereNotExists($subquery)
                        ->whereNotExists($subexRepair)
                        ->whereNotExists($subScap);
            }

            if ($request->kondisi === 'Ex-Repair') {
                return $q->whereExists($subexRepair);
            }
            if ($request->kondisi === 'Scrap') {
                return $q->whereExists($subScap);
            }
        });

        // Filter lokasi
        $query->when($request->filled('lokasi_id'), function ($q) use ($request) {
            return $q->whereIn('master_assets.KodeAsset', function ($subquery) use ($request) {
                $subquery->select('KodeAsset')
                    ->from('asset_location_histories')
                    ->where('KodeLokasi', $request->lokasi_id) // 🟢 FIXED
                    // ->whereNull('deleted_at')
                    ->groupBy('KodeLokasi', 'KodeAsset')
                    ->having(DB::raw('SUM(Jumlah)'), '>', 0);
            });
        });

        $query->when($request->filled('department_id'), function ($q) use ($request) {
            return $q->where('dept.id', $request->department_id);
        });

        $assets = $query->orderBy('master_assets.KodeAsset')->get();

        // 🟢 NEW: Cek kondisi per aset
        $assets = $assets->map(function ($asset) {
            $isRepair = DB::table('permintaanperbaikandetail as detail')
                ->join('permintaanperbaikanheader as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi')
                ->where('detail.KodeAsset', $asset->KodeAsset)
                ->where('header.Approval', "1")
                ->exists();

            $isexRepair = DB::table('permintaanperbaikandetail as detail')
                ->join('permintaanperbaikanheader as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi')
                ->where('detail.KodeAsset', $asset->KodeAsset)
                ->where('header.Approval', "2")
                ->exists();

            $isScrap = DB::table('permintaan_scrap_details as detail')
                ->join('permintaan_scrap_headers as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi')
                ->where('detail.KodeAsset', $asset->KodeAsset)
                ->where('header.Approval', "8")
                ->exists();

            $StatusText = 'Unknown';
            if ($isScrap) {
                $StatusText = 'Scrap';
            } elseif ($isRepair) {
                $StatusText = 'Repair';
            }
            elseif ($isexRepair) {
                $StatusText = 'Ex-Repair';
            } 
            else {
                $StatusText = 'Baik';
            }

            return [
                'id'             => $asset->id,
                'NoAsset'        => $asset->KodeAsset,
                'NamaAsset'      => $asset->NamaAsset,
                'PerkiraanHarga' => $asset->last_price ?? $asset->HargaBeli,
                'Jumlah'         => $asset->Jumlah,
                'PIC'            => $asset->pic_name ?? '-',
                'Departemen'     => $asset->department_name ?? '-',
                'Lokasi'         => $asset->location_name ?? 'Belum ada lokasi',
                // 🟢 NEW: tentukan status dari kondisi
                'StatusAsset'    => $StatusText,
                'TglStockOpname' => $asset->latest_so_date ? Carbon::parse($asset->latest_so_date)->format('Y-m-d') : '-',
            ];
        });

        return $assets;
    }

    /**
     * @OA\Get(
     *      path="/api/reports/stock-opname",
     *      summary="Get a report of stock opname",
     *      tags={"Reports"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="TglAwal",
     *          in="query",
     *          description="Start date for stock opname report (mandatory)",
     *          required=true,
     *          @OA\Schema(type="string", format="date", example="2025-10-01")
     *      ),
     *      @OA\Parameter(
     *          name="TglAkhir",
     *          in="query",
     *          description="End date for stock opname report (mandatory)",
     *          required=true,
     *          @OA\Schema(type="string", format="date", example="2025-10-31")
     *      ),
     *      @OA\Parameter(
     *          name="PIC",
     *          in="query",
     *          description="Filter by PIC (Employee ID) from perintah_stock_count_headers",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="NomorPerintah",
     *          in="query",
     *          description="Filter by NomorPerintah (NoTransaksi) from perintah_stock_count_headers",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="NoTransaksi", type="string", example="SO-001"),
     *                  @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-11-24"),
     *                  @OA\Property(property="KodeAsset", type="string", example="AST-001"),
     *                  @OA\Property(property="NamaAsset", type="string", example="Laptop Dell"),
     *                  @OA\Property(property="Status", type="string", example="valid")
     *              )
     *          )
     *      )
     * )
     */
    public function StockOpnameReport(Request $request)
    {
        $request->validate([
            'TglAwal'       => 'required|date',
            'TglAkhir'      => 'required|date|after_or_equal:TglAwal',
            'PIC'           => 'nullable|integer|exists:employees,id',
            'NomorPerintah' => 'nullable|string',
        ]);

        $query = DB::table('perintah_stock_count_headers as psc_header')
            ->join('perintah_stock_count_details as psc_detail', 'psc_header.id', '=', 'psc_detail.HeaderID')
            ->join('master_assets', 'psc_detail.KodeAsset', '=', 'master_assets.KodeAsset')
            ->leftJoin('header_asset_counts as hac', 'psc_header.id', '=', 'hac.perintah_id')
            ->leftJoin('detail_asset_counts as dac', function($join) {
                $join->on('hac.id', '=', 'dac.header_asset_count_id')
                     ->on('psc_detail.LineNumber', '=', 'dac.line_perintah')
                     ->on('psc_detail.KodeAsset', '=', 'dac.kode_asset_perintah');
            })
            ->leftJoin('employees as pic', 'psc_header.PIC', '=', 'pic.id')
            ->select(
                'psc_header.NoTransaksi',
                'psc_header.TglPerintah',
                'master_assets.KodeAsset',
                'master_assets.NamaAsset',
                'pic.name as PICName',
                DB::raw("CASE 
                            WHEN dac.Jumlah > 0 THEN 'valid'
                            WHEN dac.JumlahTidakValid > 0 THEN 'tidak valid'
                            ELSE 'belom scan'
                         END as Status")
            );

        // Apply filters
        $query->whereBetween('psc_header.TglPerintah', [$request->TglAwal, $request->TglAkhir]);

        $query->when($request->filled('PIC'), function ($q) use ($request) {
            return $q->where('psc_header.PIC', $request->PIC);
        });

        $query->when($request->filled('NomorPerintah'), function ($q) use ($request) {
            return $q->where('psc_header.NoTransaksi', 'like', '%' . $request->NomorPerintah . '%');
        });

        $data = $query->get();

        return response()->json($data);
    }
}