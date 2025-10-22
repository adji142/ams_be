<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLocationHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'KodeAsset',
        'KodeLokasi',
        'Jumlah'
    ];

    public function asset()
    {
        return $this->belongsTo(MasterAsset::class, 'KodeAsset', 'KodeAsset');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiAsset::class, 'KodeLokasi', 'kode_lokasi');
    }
}
