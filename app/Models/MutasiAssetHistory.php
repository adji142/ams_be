<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiAssetHistory extends Model
{
    protected $table = 'mutasi_asset_histories';

    protected $fillable = [
        'NoTransaksi',
        'TglTransaksi',
        'PIC_Lama',
        'PIC_Baru',
        'Keterangan',
        'KodeAsset',
    ];

    // Relasi ke employee (opsional)
    public function picLama()
    {
        return $this->belongsTo(Employee::class, 'PIC_Lama', 'id');
    }

    public function picBaru()
    {
        return $this->belongsTo(Employee::class, 'PIC_Baru', 'id');
    }
}
