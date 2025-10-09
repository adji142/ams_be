<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="SerahTerimaHeader",
 *     title="Serah Terima Asset Header",
 *     description="Header dokumen serah terima asset",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="NoSerahTerima", type="string", example="STA-20251009-0001"),
 *     @OA\Property(property="TglSerahTerima", type="string", format="date", example="2025-10-09"),
 *     @OA\Property(property="NomorPermintaan", type="string", example="PA-20251008-0005"),
 *     @OA\Property(property="PenerimaID", type="integer", example=3),
 *     @OA\Property(property="Keterangan", type="string", example="Serah terima pertama"),
 *     @OA\Property(property="DocStatus", type="integer", example=1, description="1=Open, 0=Close, 99=Batal"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="details",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SerahTerimaDetail")
 *     ),
 *     @OA\Property(
 *         property="employee",
 *         ref="#/components/schemas/Employee"
 *     ),
 *     @OA\Property(
 *         property="permintaan",
 *         ref="#/components/schemas/PermintaanAssetHeader"
 *     )
 * )
 */

class SerahTerimaHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'serah_terima_headers';

    protected $fillable = [
        'NoSerahTerima',
        'TglSerahTerima',
        'NomorPermintaan',
        'PenerimaID',
        'Keterangan',
        'DocStatus',
    ];

    public function details()
    {
        return $this->hasMany(SerahTerimaDetail::class, 'NoSerahTerima', 'NoSerahTerima');
    }

    public function permintaan()
    {
        return $this->belongsTo(\App\Models\PermintaanAssetHeader::class, 'NomorPermintaan', 'NoTransaksi');
    }

    public function penerima()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'PenerimaID');
    }
}
