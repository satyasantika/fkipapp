<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('asdfasdf')
            ]);

        $role = Role::create(['name' => 'admin']);
        Permission::create(['name' => 'active'])->assignRole('admin');


        $user->assignRole([$role->id]);

        $role = Role::create(['name' => 'jurusan']);
        $role->GivePermissionTo('active');
        $role = Role::create(['name' => 'keuangan']);
        $role->GivePermissionTo('active');
        $role = Role::create(['name' => 'dekanat']);
        $role->GivePermissionTo('active');

        // Import Data Akun Jurusan
        $csvData = fopen(base_path('/database/seeders/csvs/operators.csv'), 'r');
        $transRow = true;
        while (($data = fgetcsv($csvData, 555, ',')) !== false) {
            if (!$transRow) {
                User::create([
                    'departement_id'     => $data[0],
                    'name'      => $data[1],
                    'username'     => $data[2],
                    'email'     => $data[3],
                    'password'     => $data[4],
                ])->assignRole('jurusan');
            }
            $transRow = false;
        }
        fclose($csvData);

    }
}
