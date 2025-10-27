<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="PermintaanScrapDetail",
 *   type="object",
 *   required={"NoTransaksi", "NoUrut", "KodeAsset", "NamaAsset", "Qty", "KodeLokasi"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="NoTransaksi", type="string", example="SCRP-0001"),
 *   @OA\Property(property="NoUrut", type="integer", example=1),
 *   @OA\Property(property="KodeAsset", type="string", example="AST-1001"),
 *   @OA\Property(property="NamaAsset", type="string", example="Laptop Rusak"),
 *   @OA\Property(property="Qty", type="number", format="double", example=2),
 *   @OA\Property(property="KodeLokasi", type="string", example="GUD-001"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PermintaanScrapDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'NoTransaksi',
        'NoUrut',
        'KodeAsset',
        'NamaAsset',
        'Qty',
        'KodeLokasi',
        'StatusID',
        'Keterangan'
    ];

    public function header()
    {
        return $this->belongsTo(PermintaanScrapHeader::class, 'NoTransaksi', 'NoTransaksi');
    }

    /**
     * Relasi many-to-one ke MasterLokasi.
     */
    public function lokasi()
    {
        // Pastikan nama model LokasiAsset sudah benar
        return $this->belongsTo(LokasiAsset::class, 'KodeLokasi', 'kode_lokasi');
    }
}
