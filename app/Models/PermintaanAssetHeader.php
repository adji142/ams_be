<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="PermintaanAssetHeader",
 *   type="object",
 *   title="Permintaan Asset Header",
 *   description="Header transaksi permintaan asset",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="NoTransaksi", type="string", example="PA-20251008-0001"),
 *   @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-10-08"),
 *   @OA\Property(property="Requester", type="string", example="Budi Santoso"),
 *   @OA\Property(property="Keterangan", type="string", example="Permintaan laptop dan printer"),
 *  @OA\Property(property="DocStatus", type="string", example="Open"),
 *   @OA\Property(
 *       property="details",
 *       type="array",
 *       @OA\Items(ref="#/components/schemas/PermintaanAssetDetail")
 *   )
 * )
 */
class PermintaanAssetHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'NoTransaksi',
        'TglTransaksi',
        'Requester',
        'Keterangan',
        'DocStatus'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
                $latest = self::orderBy('id', 'desc')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $model->NoTransaksi = 'PA-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            });
    }

    public function details()
    {
        return $this->hasMany(PermintaanAssetDetail::class, 'NoTransaksi', 'NoTransaksi');
    }
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'Requester');
    }
}
