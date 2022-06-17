<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('users')->insert([
        //     'name' => 'Admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => Hash::make('123456789')
        // ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $super_admin = Role::create(['name' => 'super_admin']);
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        //For dev only
        //  $user = Role::where(['name' => 'user'])->first();
        //  $user= User::where('id', 2)->first();
        //  $user->roles()->attach($user);

        $user= User::where('id', 1)->first();
        $user->assignRole($super_admin);

    }
}
