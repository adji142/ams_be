<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Department",
 *     type="object",
 *     title="Department",
 *     required={"code","name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="HRD"),
 *     @OA\Property(property="name", type="string", example="Human Resources"),
 *     @OA\Property(property="description", type="string", example="Handles HR processes"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Department extends Model
{
    protected $fillable = ['code','name','description'];
}
