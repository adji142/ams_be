<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RbacSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::create(['name'=>'admin','label'=>'Administrator']);
        $userRole  = Role::create(['name'=>'user','label'=>'User']);

        $perms = [
            ['name'=>'manage-users','label'=>'Manage Users'],
            ['name'=>'manage-roles','label'=>'Manage Roles'],
            ['name'=>'manage-permissions','label'=>'Manage Permissions'],
            ['name'=>'view-assets','label'=>'View Assets'],
            ['name'=>'manage-assets','label'=>'Manage Assets'],
        ];

        foreach($perms as $p) Permission::create($p);

        // attach all perms to admin
        $adminRole->permissions()->sync(\App\Models\Permission::pluck('id')->toArray());

        // create admin user
        $admin = User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('123456')]);
        $admin->roles()->attach($adminRole->id);
    }
}
