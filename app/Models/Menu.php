<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Menu",
 *   type="object",
 *   title="Menu",
 *   description="Schema untuk Menu",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Dashboard"),
 *   @OA\Property(property="url", type="string", example="/dashboard"),
 *   @OA\Property(property="icon", type="string", example="fas fa-home"),
 *   @OA\Property(property="permission_id", type="integer", nullable=true, example=10),
 *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *   @OA\Property(property="order", type="integer", example=1),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-03T12:34:56Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-03T12:34:56Z")
 * )
 */

class Menu extends Model
{
    protected $fillable = ['name','url','icon','permission_id','parent_id','order'];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->with('children');
    }
}
