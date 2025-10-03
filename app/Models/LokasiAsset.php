<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="LokasiAsset",
 *     type="object",
 *     title="Lokasi Asset",
 *     required={"kode_lokasi","nama_lokasi","pic_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="kode_lokasi", type="string", example="LOC001"),
 *     @OA\Property(property="nama_lokasi", type="string", example="Gudang Utama"),
 *     @OA\Property(property="keterangan", type="string", example="Lokasi untuk penyimpanan utama"),
 *     @OA\Property(property="pic_id", type="integer", example=3),
 *     @OA\Property(property="asset_count", type="integer", example=120),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class LokasiAsset extends Model
{
    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'keterangan',
        'pic_id',
        'asset_count'
    ];

    public function pic()
    {
        return $this->belongsTo(Employee::class, 'pic_id');
    }
}
