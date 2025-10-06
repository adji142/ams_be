<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterAsset;

/**
 * @OA\Tag(
 *     name="MasterAssets",
 *     description="API for managing master assets"
 * )
 */
class MasterAssetController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/master-assets",
     *     summary="List all master assets",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/MasterAsset"))
     *     )
     * )
     */
    public function index()
    {
        return MasterAsset::with(['employee', 'grupAsset', 'images'])->orderBy('id', 'desc')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/master-assets",
     *     summary="Create new master asset",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"KodeAsset","NamaAsset","TglBeli","Jumlah"},
     *             @OA\Property(property="KodeAsset", type="string", example="AST-001"),
     *             @OA\Property(property="NamaAsset", type="string", example="Laptop Dell Latitude"),
     *             @OA\Property(property="TglBeli", type="string", format="date", example="2024-10-01"),
     *             @OA\Property(property="TglKapitalisasi", type="string", format="date", example="2024-10-15"),
     *             @OA\Property(property="UmurPakai", type="integer", example=5),
     *             @OA\Property(property="Keterangan", type="integer", example=1),
     *             @OA\Property(property="Jumlah", type="number", format="double", example=15000000),
     *             @OA\Property(property="PIC", type="integer", example=3, description="Employee ID as PIC")
     *         )
     *     ),
     *     @OA\Response(response=201, description="MasterAsset created", @OA\JsonContent(ref="#/components/schemas/MasterAsset")),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'KodeAsset'       => 'required|string|max:100|unique:master_assets,KodeAsset',
            'NamaAsset'       => 'required|string|max:255',
            'TglBeli'         => 'required|date',
            'TglKapitalisasi' => 'nullable|date',
            'UmurPakai'       => 'nullable|integer',
            'Keterangan'      => 'nullable|string|max:255',
            'Jumlah'          => 'required|numeric',
            'PIC'             => 'nullable|integer|exists:employees,id',
            'GrupAssetID' => 'nullable|integer|exists:grup_assets,id',
        ]);

        $asset = MasterAsset::create($data);
        return response()->json($asset->load(['employee', 'grupAsset', 'images']), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/master-assets/{id}",
     *     summary="Get master asset detail",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/MasterAsset")),
     *     @OA\Response(response=404, description="Asset not found")
     * )
     */
    public function show(MasterAsset $masterAsset)
    {
        return $masterAsset->load('employee');
    }

    /**
     * @OA\Put(
     *     path="/api/master-assets/{id}",
     *     summary="Update master asset",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MasterAsset")
     *     ),
     *     @OA\Response(response=200, description="MasterAsset updated", @OA\JsonContent(ref="#/components/schemas/MasterAsset")),
     *     @OA\Response(response=404, description="MasterAsset not found")
     * )
     */
    public function update(Request $request, MasterAsset $masterAsset)
    {
        $asset = \App\Models\MasterAsset::with('images')->findOrFail($masterAsset->id);

        $data = $request->validate([
            'GrupAssetID' => 'nullable|integer|exists:grup_assets,id',
            'KodeAsset'       => 'required|string|max:100',
            'NamaAsset' => 'required|string|max:255',
            'TglBeli' => 'nullable|date',
            'TglKapitalisasi' => 'nullable|date',
            'UmurPakai' => 'nullable|integer',
            'Keterangan' => 'nullable|string|max:255',
            'Jumlah' => 'nullable|numeric',
            'PIC' => 'nullable|integer|exists:employees,id',
        ]);

        $asset->update($data);

        // 🔥 Hapus semua file lama & record image
        if ($asset->images && count($asset->images) > 0) {
            foreach ($asset->images as $img) {
                \Storage::disk('public')->delete($img->file_path);
                $img->delete();
            }
        }

        // 🔥 Upload ulang jika ada file baru
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('uploads/assets/' . $asset->KodeAsset, 'public');
                $asset->images()->create(['file_path' => $path]);
            }
        }

        return response()->json($asset->load(['employee', 'grupAsset', 'images']));
    }

    /**
     * @OA\Delete(
     *     path="/api/master-assets/{id}",
     *     summary="Delete master asset",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="MasterAsset deleted"),
     *     @OA\Response(response=404, description="Asset not found")
     * )
     */
    public function destroy(MasterAsset $masterAsset)
    {
        $masterAsset->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * @OA\Post(
     *     path="/api/master-assets/{id}/images",
     *     summary="Upload image(s) for a Master Asset",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Images uploaded successfully"),
     *     @OA\Response(response=404, description="Asset not found")
     * )
     */
    public function uploadImages(Request $request, $id)
    {
        $asset = \App\Models\MasterAsset::findOrFail($id);

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|string', // base64 string
        ]);

        // Hapus semua gambar lama
        $asset->images()->delete();

        $uploaded = [];
        foreach ($request->images as $base64) {
            $img = $asset->images()->create(['file_path' => $base64]);
            $uploaded[] = $img;
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'data' => $uploaded,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/master-assets/{asset_id}/images/{image_id}",
     *     summary="Delete an asset image",
     *     tags={"MasterAssets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="asset_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="image_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Image deleted"),
     *     @OA\Response(response=404, description="Image not found")
     * )
     */
    public function deleteImage($asset_id, $image_id)
    {
        $image = \App\Models\MasterAssetImage::where('master_asset_id', $asset_id)
            ->where('id', $image_id)
            ->firstOrFail();

        \Storage::disk('public')->delete($image->file_path);
        $image->delete();

        return response()->json(['message' => 'Image deleted']);
    }

}
