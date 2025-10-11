<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="MutasiAssetHeader",
 *     type="object",
 *     title="Mutasi Asset Header",
 *     required={"NoTransaksi", "TglTransaksi"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="NoTransaksi", type="string", example="MT-20251009-0001"),
 *     @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-10-09"),
 *     @OA\Property(property="DocStatus", type="integer", example=1, description="1=Open, 0=Close, 99=Batal"),
 *     @OA\Property(property="Keterangan", type="string", example="Pemindahan asset ke gudang baru"),
 *     @OA\Property(property="details", type="array", @OA\Items(ref="#/components/schemas/MutasiAssetDetail"))
 * )
 */
class MutasiAssetHeader extends Model
{
    use SoftDeletes;

    protected $fillable = ['NoTransaksi', 'TglTransaksi', 'DocStatus', 'Keterangan'];

    public function details()
    {
        return $this->hasMany(MutasiAssetDetail::class, 'NoTransaksi', 'NoTransaksi');
    }
}
