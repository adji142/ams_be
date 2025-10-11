<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="PermintaanPerbaikanHeader",
 *     title="Permintaan Perbaikan Header",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="NoTransaksi", type="string", example="PPB-20251009-0001"),
 *     @OA\Property(property="TglTransaksi", type="string", format="date"),
 *     @OA\Property(property="DocStatus", type="integer"),
 *     @OA\Property(property="Keterangan", type="string"),
 *     @OA\Property(property="Approval", type="integer", description="0: Pending, 1: Approve, 2: Selesai, 9: Reject"),
 *     @OA\Property(property="KeteranganApproval", type="string"),
 *     @OA\Property(property="ApproveDate", type="string", format="date-time"),
 *     @OA\Property(property="ApproveBy", type="string")
 * )
 */
class PermintaanPerbaikanHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permintaanperbaikanheader';
    protected $fillable = [
        'NoTransaksi',
        'TglTransaksi',
        'DocStatus',
        'Keterangan',
        'Approval',
        'KeteranganApproval',
        'ApproveDate',
        'ApproveBy',
    ];

    public function details()
    {
        return $this->hasMany(PermintaanPerbaikanDetail::class, 'NoTransaksi', 'NoTransaksi');
    }
}
