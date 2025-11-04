<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 * schema="DetailAssetCount",
 * title="Detail Asset Count",
 * description="Model untuk detail item pada transaksi penghitungan aset.",
 * @OA\Property(property="id", type="integer", format="int64", description="ID unik detail", example=1),
 * @OA\Property(property="header_asset_count_id", type="integer", description="ID header terkait", example=1),
 * @OA\Property(property="AssetID", type="integer", description="ID Master Aset", example=101),
 * @OA\Property(property="LineNumber", type="integer", description="Nomor urut baris", example=1),
 * @OA\Property(property="Jumlah", type="integer", description="Jumlah aset yang dihitung", example=50),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Waktu pembuatan record"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Waktu pembaruan record")
 * )
 */
class DetailAssetCount extends Model
{
    use HasFactory;
    
    protected $table = 'detail_asset_counts';

    protected $fillable = [
        'header_asset_count_id',
        'AssetID',
        'LineNumber',
        'Jumlah',
        'DetailLokasiID',
        'line_perintah',
        'kode_asset_perintah',
        'JumlahTidakValid'
    ];
    
    /**
     * Otomatis update timestamp 'updated_at' di header
     * setiap kali detail diubah/dibuat/dihapus.
     */
    protected $touches = ['header'];

    /**
     * Relasi many-to-one ke HeaderAssetCount.
     */
    public function header()
    {
        return $this->belongsTo(HeaderAssetCount::class, 'header_asset_count_id');
    }

    /**
     * Relasi many-to-one ke MasterAsset.
     */
    public function asset()
    {
        // Pastikan nama model MasterAsset sudah benar
        return $this->belongsTo(MasterAsset::class, 'AssetID');
    }
}