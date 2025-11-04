<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *   schema="PerintahStockCountHeader",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="NoTransaksi", type="string", example="PSC-20251102-001"),
 *   @OA\Property(property="TglPerintah", type="string", format="date", example="2025-11-02"),
 *   @OA\Property(property="PIC", type="integer", example=5),
 *   @OA\Property(property="Keterangan", type="string", example="Stock opname gudang A"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */
class PerintahStockCountHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['NoTransaksi', 'TglPerintah', 'PIC', 'Keterangan'];

    public function details()
    {
        return $this->hasMany(PerintahStockCountDetail::class, 'HeaderID');
    }
    public function pic()
    {
        return $this->belongsTo(Employee::class, 'PIC', 'id');
    }
    public function assetCountHeader()
    {
        return $this->hasOne(HeaderAssetCount::class, 'perintah_id', 'id');
    }
}
