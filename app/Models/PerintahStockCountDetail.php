<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *   schema="PerintahStockCountDetail",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="HeaderID", type="integer", example=1),
 *   @OA\Property(property="KodeAsset", type="string", example="AST-001"),
 *   @OA\Property(property="LineNumber", type="integer", example=1),
 *   @OA\Property(property="KodeLokasi", type="string", example="GUD-A"),
 *   @OA\Property(property="Jumlah", type="number", format="float", example=5),
 * )
 */
class PerintahStockCountDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['HeaderID', 'KodeAsset', 'LineNumber', 'KodeLokasi', 'Jumlah'];

    public function header()
    {
        return $this->belongsTo(PerintahStockCountHeader::class, 'HeaderID');
    }
    /**
     * Relasi many-to-one ke MasterLokasi.
     */
    public function lokasi()
    {
        // Pastikan nama model LokasiAsset sudah benar
        return $this->belongsTo(LokasiAsset::class, 'KodeLokasi', 'kode_lokasi');
    }

    /**
     * Relasi many-to-one ke Master Asset.
     */
    public function asset()
    {
        return $this->belongsTo(MasterAsset::class, 'KodeAsset', 'KodeAsset');
    }
}
