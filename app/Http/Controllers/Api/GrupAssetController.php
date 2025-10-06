<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GrupAsset;

class GrupAssetController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/grupasset",
     *     summary="Get list of Grup Asset",
     *     tags={"Grup Asset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/GrupAsset"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(GrupAsset::get());
    }

    /**
     * @OA\Post(
     *     path="/api/grupasset",
     *     summary="Create a new Grup Asset",
     *     tags={"Grup Asset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="kode_Grup", type="string", example="GRP001"),
     *             @OA\Property(property="nama_Grup", type="string", example="Gudang Utama"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="GrupAsset created",
     *         @OA\JsonContent(ref="#/components/schemas/GrupAsset")
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_Grup' => 'required|string|unique:grup_assets,kode_Grup',
            'nama_Grup' => 'required|string'
        ]);

        $grupAsset = GrupAsset::create($data);
        return response()->json(['message'=>'GrupAsset created','grupAsset'=>$grupAsset], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/grupasset/{id}",
     *     summary="Get Grup Asset detail",
     *     tags={"Grup Asset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GrupAsset")
     *     ),
     *     @OA\Response(response=404, description="Grup Asset not found")
     * )
     */
    public function show(GrupAsset $grupAsset)
    {
        return response()->json($grupAsset);
    }

    /**
     * @OA\Put(
     *     path="/api/grupasset/{id}",
     *     summary="Update a Grup Asset",
     *     tags={"Grup Asset"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kode_Grup","nama_Grup"},
     *             @OA\Property(property="kode_Grup", type="string", example="GRP001"),
     *             @OA\Property(property="nama_Grup", type="string", example="Gudang Utama"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grup Asset updated",
     *         @OA\JsonContent(ref="#/components/schemas/GrupAsset")
     *     ),
     *     @OA\Response(response=404, description="Grup Asset not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'kode_Grup' => 'required|string|unique:grup_assets,kode_Grup',
            'nama_Grup' => 'required|string'
        ]);

        $grupAsset->update($data);
        return response()->json(['message'=>'Grup Asset updated','grupasset'=>$grupAsset]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
