<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="GrupAsset",
 *     type="object",
 *     title="Grup Asset",
 *     required={"kode_Grup","nama_Grup"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="kode_Grup", type="string", example="GRP001"),
 *     @OA\Property(property="nama_Grup", type="string", example="Gudang Utama"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class GrupAsset extends Model
{
    protected $fillable = [
        'kode_Grup',
        'nama_Grup'
    ];

    public function assets()
    {
        return $this->hasMany(\App\Models\MasterAsset::class, 'GrupAssetID');
    }
}
