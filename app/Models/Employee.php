<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Employee",
 *     type="object",
 *     title="Employee",
 *     required={"nik","name","department_id","tgl_masuk","email"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nik", type="string", example="EMP001"),
 *     @OA\Property(property="name", type="string", example="Budi Santoso"),
 *     @OA\Property(property="department_id", type="integer", example=2),
 *     @OA\Property(property="tgl_masuk", type="string", format="date", example="2020-01-15"),
 *     @OA\Property(property="tgl_resign", type="string", format="date", nullable=true, example="2023-05-01"),
 *     @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
 *     @OA\Property(property="tgl_lahir", type="string", format="date", example="1995-04-20"),
 *     @OA\Property(property="email", type="string", example="budi@example.com"),
 *     @OA\Property(property="no_tlp", type="string", example="+628123456789")
 * )
 */
class Employee extends Model
{
    protected $fillable = [
        'nik','name','department_id','tgl_masuk','tgl_resign',
        'tempat_lahir','tgl_lahir','email','no_tlp'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
