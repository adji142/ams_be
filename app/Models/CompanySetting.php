<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CompanySetting",
 *     type="object",
 *     title="CompanySetting",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="NamaPerusahaan", type="string", example="PT. Contoh Jaya"),
 *     @OA\Property(property="Alamat1", type="string", example="Jl. Jendral Sudirman No. 1"),
 *     @OA\Property(property="Alamat2", type="string", example="Jakarta Selatan, Indonesia"),
 *     @OA\Property(property="Email", type="string", example="info@contohjaya.com"),
 *     @OA\Property(property="NoTlp", type="string", example="021-12345678"),
 *     @OA\Property(property="Icon", type="string", format="byte", description="Base64 encoded image"),
 *     @OA\Property(property="LabelWidth", type="number", format="float", example=100.5),
 *     @OA\Property(property="LabelHeight", type="number", format="float", example=50.5),
 *     @OA\Property(property="H1Size", type="number", format="float", example=24.0),
 *     @OA\Property(property="H2Size", type="number", format="float", example=18.0),
 *     @OA\Property(property="PSize", type="number", format="float", example=12.0),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'NamaPerusahaan',
        'Alamat1',
        'Alamat2',
        'Email',
        'NoTlp',
        'Icon',
        'LabelWidth',
        'LabelHeight',
        'H1Size',
        'H2Size',
        'PSize',
    ];
}
