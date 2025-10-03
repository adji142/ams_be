<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LokasiAsset;

/**
 * @OA\Tag(
 *     name="LokasiAssets",
 *     description="API for managing asset locations"
 * )
 */
class LokasiAssetController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/lokasi-assets",
     *     summary="List all asset locations",
     *     tags={"LokasiAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/LokasiAsset"))
     *     )
     * )
     */
    public function index()
    {
        return LokasiAsset::with('pic')->paginate(15);
    }

    /**
     * @OA\Post(
     *     path="/api/lokasi-assets",
     *     summary="Create a new asset location",
     *     tags={"LokasiAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kode_lokasi","nama_lokasi","pic_id"},
     *             @OA\Property(property="kode_lokasi", type="string", example="LOC002"),
     *             @OA\Property(property="nama_lokasi", type="string", example="Gudang Sparepart"),
     *             @OA\Property(property="keterangan", type="string", example="Penyimpanan sparepart"),
     *             @OA\Property(property="pic_id", type="integer", example=4),
     *             @OA\Property(property="asset_count", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Lokasi Asset created", @OA\JsonContent(ref="#/components/schemas/LokasiAsset"))
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_lokasi' => 'required|unique:lokasi_assets,kode_lokasi',
            'nama_lokasi' => 'required|string',
            'keterangan' => 'nullable|string',
            'pic_id' => 'required|exists:employees,id',
            'asset_count' => 'nullable|integer|min:0'
        ]);

        $lokasi = LokasiAsset::create($data);
        return response()->json($lokasi->load('pic'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/lokasi-assets/{id}",
     *     summary="Get detail of an asset location",
     *     tags={"LokasiAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/LokasiAsset")),
     *     @OA\Response(response=404, description="Lokasi not found")
     * )
     */
    public function show(LokasiAsset $lokasiAsset)
    {
        return $lokasiAsset->load('pic');
    }

    /**
     * @OA\Put(
     *     path="/api/lokasi-assets/{id}",
     *     summary="Update asset location",
     *     tags={"LokasiAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LokasiAsset")),
     *     @OA\Response(response=200, description="Lokasi Asset updated", @OA\JsonContent(ref="#/components/schemas/LokasiAsset")),
     *     @OA\Response(response=404, description="Lokasi not found")
     * )
     */
    public function update(Request $request, LokasiAsset $lokasiAsset)
    {
        $data = $request->validate([
            'kode_lokasi' => 'required|string|unique:lokasi_assets,kode_lokasi,'.$lokasiAsset->id,
            'nama_lokasi' => 'required|string',
            'keterangan' => 'nullable|string',
            'pic_id' => 'required|exists:employees,id',
            'asset_count' => 'nullable|integer|min:0'
        ]);

        $lokasiAsset->update($data);
        return response()->json($lokasiAsset->load('pic'));
    }

    /**
     * @OA\Delete(
     *     path="/api/lokasi-assets/{id}",
     *     summary="Delete asset location",
     *     tags={"LokasiAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lokasi Asset deleted"),
     *     @OA\Response(response=404, description="Lokasi not found")
     * )
     */
    public function destroy(LokasiAsset $lokasiAsset)
    {
        $lokasiAsset->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
