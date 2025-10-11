<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="PermintaanPerbaikanDetail",
 *     title="Permintaan Perbaikan Detail",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="NoTransaksi", type="string"),
 *     @OA\Property(property="NoUrut", type="integer"),
 *     @OA\Property(property="KodeAsset", type="string"),
 *     @OA\Property(property="NamaAsset", type="string"),
 *     @OA\Property(property="Qty", type="number", format="float"),
 *     @OA\Property(property="KodeLokasi", type="string")
 * )
 */
class PermintaanPerbaikanDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permintaanperbaikandetail';
    protected $fillable = [
        'NoTransaksi',
        'NoUrut',
        'KodeAsset',
        'NamaAsset',
        'Qty',
        'KodeLokasi',
    ];

    public function header()
    {
        // Ganti Model::class menjadi model Header Anda
        return $this->belongsTo(PermintaanPerbaikanHeader::class, 'NoTransaksi', 'NoTransaksi'); 
    }
}
