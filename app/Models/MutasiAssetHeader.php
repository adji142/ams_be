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
 *     @OA\Property(property="DocStatus", type="integer", example=1),
 *     @OA\Property(property="Keterangan", type="string", example="Pemindahan asset ke gudang baru"),

 *     @OA\Property(property="PIC_Lama", type="string", example="EMP001"),
 *     @OA\Property(property="PIC_Baru", type="string", example="EMP010"),

 *     @OA\Property(property="details", type="array", @OA\Items(ref="#/components/schemas/MutasiAssetDetail"))
 * )
 */
class MutasiAssetHeader extends Model
{
    use SoftDeletes;

    protected $fillable = ['NoTransaksi', 'TglTransaksi', 'DocStatus', 'Keterangan','PIC_Lama','PIC_Baru'];

    public function details()
    {
        return $this->hasMany(MutasiAssetDetail::class, 'NoTransaksi', 'NoTransaksi');
    }

    // 🔥 Relasi PIC Lama → employees.id
    public function picLama()
    {
        return $this->belongsTo(Employee::class, 'PIC_Lama', 'id');
    }

    // 🔥 Relasi PIC Baru → employees.id
    public function picBaru()
    {
        return $this->belongsTo(Employee::class, 'PIC_Baru', 'id');
    }
}
