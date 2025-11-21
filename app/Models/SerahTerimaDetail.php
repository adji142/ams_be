<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="SerahTerimaDetail",
 *     title="Serah Terima Asset Detail",
 *     description="Detail baris dari serah terima asset",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="NoSerahTerima", type="string", example="STA-20251009-0001"),
 *     @OA\Property(property="NomorPermintaan", type="string", example="PA-20251008-0005"),
 *     @OA\Property(property="NoUrutPermintaan", type="integer", example=1),
 *     @OA\Property(property="KodeAsset", type="string", example="AST-001"),
 *     @OA\Property(property="NamaAsset", type="string", example="Laptop Dell XPS 13"),
 *     @OA\Property(property="QtyDiterima", type="number", format="double", example=2),
 *     @OA\Property(property="EstimasiHarga", type="number", format="double", example=14500000),
 *     @OA\Property(property="KodeLokasi", type="string", example="LOC-001"),
 *     @OA\Property(property="Keterangan", type="string", example="Serah sebagian pertama"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="permintaan_detail",
 *         ref="#/components/schemas/PermintaanAssetDetail"
 *     )
 * )
 */

class SerahTerimaDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'serah_terima_details';

    protected $fillable = [
        'NoSerahTerima',
        'NomorPermintaan',
        'NoUrutPermintaan',
        'KodeAsset',
        'NamaAsset',
        'QtyDiterima',
        'EstimasiHarga',
        'Keterangan',
        'KodeLokasi'
    ];

    public function header()
    {
        return $this->belongsTo(SerahTerimaHeader::class, 'NoSerahTerima', 'NoSerahTerima');
    }

    public function permintaanDetail()
    {
        // relasi ke permintaan_asset_details (NoTransaksi + NoUrut)
        return $this->hasOne(\App\Models\PermintaanAssetDetail::class, 'NoUrut', 'NoUrutPermintaan')
            ->whereColumn('permintaan_asset_details.NoTransaksi', 'serah_terima_details.NomorPermintaan');
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
