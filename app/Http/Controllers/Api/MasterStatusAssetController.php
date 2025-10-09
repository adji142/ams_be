<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterStatusAsset;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *   name="MasterStatusAsset",
 *   description="API untuk master status asset"
 * )
 */
class MasterStatusAssetController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/master-status-assets",
     *   summary="Ambil semua status asset",
     *   tags={"MasterStatusAsset"},
     *   @OA\Response(response=200, description="Sukses", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/MasterStatusAsset")))
     * )
     */
    public function index()
    {
        return response()->json(MasterStatusAsset::all());
    }

    /**
     * @OA\Post(
     *   path="/api/master-status-assets",
     *   summary="Tambah status asset",
     *   tags={"MasterStatusAsset"},
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"NamaStatusAsset"},
     *       @OA\Property(property="NamaStatusAsset", type="string", example="Rusak"),
     *       @OA\Property(property="isDefault", type="boolean", example=false)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Berhasil menambahkan data", @OA\JsonContent(ref="#/components/schemas/MasterStatusAsset"))
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'NamaStatusAsset' => 'required|string|max:100|unique:master_status_assets,NamaStatusAsset',
            'isDefault' => 'boolean'
        ]);

        // Jika set default, matikan default status lain
        if (!empty($validated['isDefault']) && $validated['isDefault']) {
            MasterStatusAsset::where('isDefault', true)->update(['isDefault' => false]);
        }

        $status = MasterStatusAsset::create($validated);
        return response()->json($status, 201);
    }

    /**
     * @OA\Put(
     *   path="/api/master-status-assets/{id}",
     *   summary="Update status asset",
     *   tags={"MasterStatusAsset"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/MasterStatusAsset")),
     *   @OA\Response(response=200, description="Sukses", @OA\JsonContent(ref="#/components/schemas/MasterStatusAsset"))
     * )
     */
    public function update(Request $request, $id)
    {
        $status = MasterStatusAsset::findOrFail($id);

        $validated = $request->validate([
            'NamaStatusAsset' => 'required|string|max:100|unique:master_status_assets,NamaStatusAsset,' . $id,
            'isDefault' => 'boolean'
        ]);

        if (!empty($validated['isDefault']) && $validated['isDefault']) {
            MasterStatusAsset::where('isDefault', true)->update(['isDefault' => false]);
        }

        $status->update($validated);
        return response()->json($status);
    }

    /**
     * @OA\Delete(
     *   path="/api/master-status-assets/{id}",
     *   summary="Hapus status asset",
     *   tags={"MasterStatusAsset"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Berhasil dihapus")
     * )
     */
    public function destroy($id)
    {
        $status = MasterStatusAsset::findOrFail($id);
        $status->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
