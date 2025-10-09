<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="MasterStatusAsset",
 *   type="object",
 *   title="MasterStatusAsset",
 *   description="Status dari aset (Aktif, Rusak, Scrap, dll)",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="NamaStatusAsset", type="string", example="Aktif"),
 *   @OA\Property(property="isDefault", type="boolean", example=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class MasterStatusAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'NamaStatusAsset',
        'isDefault',
    ];
}
