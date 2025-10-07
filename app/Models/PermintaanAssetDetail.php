<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="PermintaanAssetDetail",
 *   type="object",
 *   title="Permintaan Asset Detail",
 *   description="Detail transaksi permintaan asset",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="NoTransaksi", type="string", example="PA-20251008-0001"),
 *   @OA\Property(property="NoUrut", type="integer", example=1),
 *   @OA\Property(property="KodeAsset", type="string", example="AST-001"),
 *   @OA\Property(property="NamaAsset", type="string", example="Laptop Lenovo Thinkpad"),
 *   @OA\Property(property="Qty", type="number", format="double", example=2),
 *   @OA\Property(property="EstimasiHarga", type="number", format="double", example=12000000),
 *   @OA\Property(property="QtySerahTerima", type="number", format="double", example=0)
 * )
 */
class PermintaanAssetDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'NoTransaksi',
        'NoUrut',
        'KodeAsset',
        'NamaAsset',
        'Qty',
        'EstimasiHarga',
        'QtySerahTerima',
    ];

    public function header()
    {
        return $this->belongsTo(PermintaanAssetHeader::class, 'NoTransaksi', 'NoTransaksi');
    }
}
