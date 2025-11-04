<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="HeaderAssetCount",
 * title="Header Asset Count",
 * description="Model untuk header transaksi penghitungan aset.",
 * @OA\Property(property="id", type="integer", format="int64", description="ID unik header", example=1),
 * @OA\Property(property="NoTransaksi", type="string", description="Nomor unik transaksi", example="AC/2025/10/001"),
 * @OA\Property(property="TglTransaksi", type="string", format="date", description="Tanggal transaksi", example="2025-10-17"),
 * @OA\Property(property="PICID", type="integer", description="ID PIC (Employee)", example=5),
 * @OA\Property(property="LokasiID", type="integer", description="ID Lokasi Aset", example=12),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Waktu pembuatan record"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Waktu pembaruan record")
 * )
 */
class HeaderAssetCount extends Model
{
    use HasFactory;

    protected $table = 'header_asset_counts';

    protected $fillable = [
        'NoTransaksi',
        'TglTransaksi',
        'PICID',
        'LokasiID',
        'perintah_id',
        'JamMulai',
        'JamSelesai'
    ];

    /**
     * Relasi one-to-many ke DetailAssetCount.
     */
    public function details()
    {
        return $this->hasMany(\App\Models\DetailAssetCount::class, 'header_asset_count_id');
    }

    /**
     * Relasi many-to-one ke Employee (PIC).
     */
    public function pic()
    {
        // Pastikan nama model Employee sudah benar
        return $this->belongsTo(\App\Models\Employee::class, 'PICID');
    }

    /**
     * Relasi many-to-one ke LokasiAsset.
     */
    public function lokasi()
    {
        // Pastikan nama model LokasiAsset sudah benar
        return $this->belongsTo(\App\Models\LokasiAsset::class, 'LokasiID');
    }

    public function perintah()
    {
        return $this->belongsTo(PerintahStockCountHeader::class, 'perintah_id', 'id');
    }
}