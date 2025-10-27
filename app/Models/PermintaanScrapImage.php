<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanScrapImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'permintaan_scrap_header_id',
        'image_base64',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return $this->image_base64 ?? '';
    }
}
