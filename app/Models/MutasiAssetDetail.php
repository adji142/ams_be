<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="MutasiAssetDetail",
 *     type="object",
 *     title="Mutasi Asset Detail",
 *     required={"NoTransaksi", "KodeAsset", "Qty"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="NoTransaksi", type="string", example="MT-20251009-0001"),
 *     @OA\Property(property="NoUrut", type="integer", example=1),
 *     @OA\Property(property="KodeAsset", type="string", example="AST-0001"),
 *     @OA\Property(property="NamaAsset", type="string", example="Laptop Dell XPS 13"),
 *     @OA\Property(property="Qty", type="number", format="double", example=2),
 *     @OA\Property(property="KodeLokasiAsal", type="string", example="LOC-001"),
 *     @OA\Property(property="KodeLokasiTujuan", type="string", example="LOC-002")
 * )
 */
class MutasiAssetDetail extends Model
{
    protected $fillable = [
        'NoTransaksi', 'NoUrut', 'KodeAsset', 'NamaAsset', 'Qty', 'KodeLokasiAsal', 'KodeLokasiTujuan'
    ];
}
