<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="MasterAssetImage",
 *   type="object",
 *   title="MasterAssetImage",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="master_asset_id", type="integer", example=1),
 *   @OA\Property(property="file_path", type="string", example="uploads/assets/AST-001/image1.jpg"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class MasterAssetImage extends Model
{
    use HasFactory;

    protected $fillable = ['master_asset_id', 'file_path'];

    public function asset()
    {
        return $this->belongsTo(MasterAsset::class, 'master_asset_id');
    }
}
