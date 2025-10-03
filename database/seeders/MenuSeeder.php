<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $permUsers  = Permission::where('name','manage-users')->first();
        $permAssets = Permission::where('name','manage-assets')->first();

        Menu::create(['name'=>'Dashboard','url'=>'/dashboard','icon'=>'home','order'=>1]);
        Menu::create(['name'=>'Users','url'=>'/users','icon'=>'users','permission_id'=>$permUsers->id,'order'=>2]);
        Menu::create(['name'=>'Assets','url'=>'/assets','icon'=>'box','permission_id'=>$permAssets->id,'order'=>3]);
    }
}
