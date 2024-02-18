<?php

namespace Database\Seeders;

use App\Models\Lecture;
use App\Models\Student;
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
        // Import Data Operator
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

        // Import Data Dosen
        $csvData = fopen(base_path('/database/seeders/csvs/lectures.csv'), 'r');
        $transRow = true;
        while (($data = fgetcsv($csvData, 555, ',')) !== false) {
            if (!$transRow) {
                Lecture::create([
                    'departement_id'  => $data[0],
                    'name'      => $data[1],
                    'gelar_depan'     => $data[2],
                    'gelar_belakang'     => $data[3],
                    'nidn'     => $data[4],
                    'nip'     => $data[5],
                    'tempat_lahir'     => $data[6],
                    'tanggal_lahir'     => $data[7],
                    'nik'     => $data[8],
                    'npwp'     => $data[9],
                    'phone'     => $data[10],
                    'email'     => $data[11],
                    'alamat'     => $data[12],
                    'golongan'     => $data[13],
                    'kualifikasi'     => $data[14],
                    'jafung'     => $data[15],
                    'rekening'     => $data[16],
                ]);
            }
            $transRow = false;
        }
        fclose($csvData);

        // Import Data Mahasiswa
        $csvData = fopen(base_path('/database/seeders/csvs/students.csv'), 'r');
        $transRow = true;
        while (($data = fgetcsv($csvData, 555, ',')) !== false) {
            if (!$transRow) {
                Student::create([
                    'departement_id'  => $data[0],
                    'nim'      => $data[1],
                    'name'      => $data[2],
                    'email'     => $data[3],
                    'phone'     => $data[4],
                ]);
            }
            $transRow = false;
        }
        fclose($csvData);
    }
}
