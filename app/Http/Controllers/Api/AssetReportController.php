<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterAsset; // Sesuaikan dengan path model Anda
use Illuminate\Support\Facades\DB;

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
        // 1. Validasi Input Filter (hanya kondisi dan lokasi)
        $request->validate([
            'kondisi'   => 'nullable|string|in:Baik,Repair',
            'lokasi_id' => 'nullable|integer|exists:lokasi_assets,id',
        ]);

        // Subquery untuk efisiensi (tetap sama)
        $latestLocationSubquery = DB::table('asset_location_histories')
            ->select('KodeAsset', DB::raw('MAX(id) as last_id'))
            ->groupBy('KodeAsset');

        $latestPriceSubquery = DB::table('serah_terima_details')
            ->select('KodeAsset', DB::raw('MAX(id) as last_price_id'))
            ->whereNull('deleted_at')
            ->groupBy('KodeAsset');

        // 2. Query Builder Utama (tetap sama)
        $query = MasterAsset::query()
            ->select([
                'master_assets.*',
                'pic.name as pic_name',
                'dept.name as department_name',
                'stat.NamaStatusAsset as status_name',
                'loc.nama_lokasi as location_name',
                'std.EstimasiHarga as last_price'
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
            ->leftJoin('serah_terima_details as std', 'latest_price_sub.last_price_id', '=', 'std.id');


        // 3. (DIUBAH) Logika Filtering Dinamis Sesuai Aturan Baru
        $query->when($request->filled('kondisi'), function ($q) use ($request) {
            // Definisikan subquery untuk mencari aset dalam perbaikan yang disetujui
            $subquery = function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('permintaanperbaikandetail as detail')
                    ->join('permintaanperbaikanheader as header', 'detail.NoTransaksi', '=', 'header.NoTransaksi') // Sesuaikan foreign key
                    ->whereColumn('detail.KodeAsset', 'master_assets.KodeAsset')
                    ->whereNull('detail.deleted_at')
                    ->where('header.Approval', 1);
            };

            // Jika kondisi Repair, cari aset YANG ADA di subquery
            if ($request->kondisi === 'Repair') {
                return $q->whereExists($subquery);
            }

            // Jika kondisi Baik, cari aset YANG TIDAK ADA di subquery
            if ($request->kondisi === 'Baik') {
                return $q->whereNotExists($subquery);
            }
        });

        // Filter Lokasi (logika ini sudah sesuai dengan permintaan Anda)
        $query->when($request->filled('lokasi_id'), function ($q) use ($request) {
             return $q->whereIn('master_assets.KodeAsset', function ($subquery) use ($request) {
                $subquery->select('KodeAsset')
                    ->from('asset_location_histories')
                    ->where('id', $request->lokasi_id)
                    ->groupBy('id', 'KodeAsset')
                    ->having(DB::raw('SUM(Jumlah)'), '>', 0);
            });
        });

        // 4. Eksekusi dan Transformasi Data (tetap sama)
        $assets = $query->orderBy('master_assets.KodeAsset')->get();

        return $assets->map(function ($asset) {
            return [
                'id'             => $asset->id,
                'NoAsset'        => $asset->KodeAsset,
                'NamaAsset'      => $asset->NamaAsset,
                'PerkiraanHarga' => $asset->last_price ?? $asset->HargaBeli,
                'Jumlah'         => $asset->Jumlah,
                'PIC'            => $asset->pic_name ?? '-',
                'Departemen'     => $asset->department_name ?? '-',
                'Lokasi'         => $asset->location_name ?? 'Belum ada lokasi',
                'StatusAsset'    => $asset->status_name ?? '-',
            ];
        });
    }
}