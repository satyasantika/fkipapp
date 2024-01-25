<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StakeholdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Import Data Jurusan
        $csvData = fopen(base_path('/database/seeders/csvs/departements.csv'), 'r');
        $transRow = true;
        while (($data = fgetcsv($csvData, 555, ',')) !== false) {
            if (!$transRow) {
                Departement::create([
                    'id'  => $data[0],
                    'name'      => $data[1],
                    'mapel'     => $data[2],
                    'singkatan'     => $data[3],
                ]);
            }
            $transRow = false;
        }
        fclose($csvData);
    }
}
