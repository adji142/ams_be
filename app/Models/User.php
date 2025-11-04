<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public function employee() {
        return $this->belongsTo(Employee::class, 'KaryawanID');
    }

    protected $fillable = ['name','email','password', 'UseForMobile', 'KaryawanID'];
    protected $hidden = ['password','remember_token'];

    // relasi
    public function roles() {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function allPermissions()
    {
        return DB::table('permissions')
            ->select('permissions.*')
            ->join('role_permission', 'permissions.id', '=', 'role_permission.permission_id')
            ->join('roles', 'roles.id', '=', 'role_permission.role_id')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $this->id)
            ->distinct()
            ->get();
    }

    // cek permission (langsung atau via role)
    public function hasPermission(string $perm): bool
    {
        // direct permission
        if ($this->permissions()->where('name', $perm)->exists()) return true;

        // permission via role
        return $this->roles()->whereHas('permissions', function($q) use ($perm) {
            $q->where('name', $perm);
        })->exists();
    }
}
