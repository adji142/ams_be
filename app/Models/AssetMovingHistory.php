<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMovingHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'KodeAsset',
        'KodeLokasi',
        'NoReff',
        'BaseReff',
        'Jumlah'
    ];
}
