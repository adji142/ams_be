<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="MasterAsset",
 *   type="object",
 *   title="MasterAsset",
 *   description="Data master aset perusahaan",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="KodeAsset", type="string", example="AST-001"),
 *   @OA\Property(property="NamaAsset", type="string", example="Laptop Dell Latitude"),
 *   @OA\Property(property="TglBeli", type="string", format="date", example="2024-10-01"),
 *   @OA\Property(property="TglKapitalisasi", type="string", format="date", example="2024-10-15"),
 *   @OA\Property(property="UmurPakai", type="integer", example=5),
 *   @OA\Property(property="Keterangan", type="integer", example=1),
 *   @OA\Property(property="Jumlah", type="number", format="double", example=15000000),
 *   @OA\Property(property="PIC", type="integer", example=3, description="Employee ID as PIC"),
 *   @OA\Property(property="employee", ref="#/components/schemas/Employee"),
 *   @OA\Property(property="GrupAssetID", type="integer", example=2),
 *   @OA\Property(property="grup_asset", ref="#/components/schemas/GrupAsset"),
 *   @OA\Property(property="StatusID", type="integer", example=1),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-06T12:34:56Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T12:34:56Z")
 * )
 */
class MasterAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'GrupAssetID',
        'KodeAsset',
        'NamaAsset',
        'TglBeli',
        'TglKapitalisasi',
        'UmurPakai',
        'Keterangan',
        'Jumlah',
        'PIC',
        'StatusID'
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'PIC');
    }
    public function images()
    {
        return $this->hasMany(\App\Models\MasterAssetImage::class, 'master_asset_id');
    }
    public function grupAsset()
    {
        return $this->belongsTo(\App\Models\GrupAsset::class, 'GrupAssetID');
    }
    public function status()
    {
        return $this->belongsTo(\App\Models\MasterStatusAsset::class, 'StatusID');
    }
}
