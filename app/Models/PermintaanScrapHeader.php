<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PermintaanScrapImage;

/**
 * @OA\Schema(
 *   schema="PermintaanScrapHeader",
 *   type="object",
 *   required={"NoTransaksi", "TglTransaksi", "Requester"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="NoTransaksi", type="string", example="SCRP-0001"),
 *   @OA\Property(property="TglTransaksi", type="string", format="date", example="2025-10-09"),
 *   @OA\Property(property="Requester", type="integer", example=101),
 *   @OA\Property(property="DocStatus", type="integer", example=1, description="0=Close, 1=Open, 99=Batal"),
 *   @OA\Property(property="Keterangan", type="string", example="Scrap aset rusak"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="Approval", type="integer", example=1, description="0: Pending, 1: Approve, 9: Reject"),
 *   @OA\Property(property="KeteranganApproval", type="string", example="Disetujui oleh kepala gudang"),
 *   @OA\Property(property="ApproveDate", type="string", format="date-time", example="2025-10-09 10:00:00"),
 *   @OA\Property(property="ApproveBy", type="integer", example=1),
 *   @OA\Property(
 *     property="details",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/PermintaanScrapDetail")
 *   )
 * )
 */
class PermintaanScrapHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'NoTransaksi',
        'TglTransaksi',
        'Requester',
        'DocStatus',
        'Keterangan',
        'Approval',
        'KeteranganApproval',
        'ApproveDate',
        'ApproveBy',
    ];

    protected $appends = ['details_with_images'];

    public function getDetailsWithImagesAttribute()
    {
        if ($this->relationLoaded('details') && $this->relationLoaded('images')) {
            return $this->details->map(function ($detail) {
                $detail->images = $this->images->map(function ($image) {
                    return [
                        'uid' => (string) $image->id,
                        'name' => 'image-' . $image->id . '.png',
                        'status' => 'done',
                        'url' => $image->url,
                    ];
                });
                return $detail;
            });
        }
        return $this->details;
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'Requester', 'id');
    }

    public function details()
    {
        return $this->hasMany(PermintaanScrapDetail::class, 'NoTransaksi', 'NoTransaksi');
    }

    public function images()
    {
        return $this->hasMany(PermintaanScrapImage::class, 'permintaan_scrap_header_id');
    }
}
