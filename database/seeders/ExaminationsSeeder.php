<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\ExamPayment;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExaminationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Import Data Tipe Ujian
        $csvData = fopen(base_path('/database/seeders/csvs/examtypes.csv'), 'r');
        $transRow = true;
        while (($data = fgetcsv($csvData, 555, ',')) !== false) {
            if (!$transRow) {
                ExamType::create([
                    'nama_ujian'      => $data[0],
                    'kode_ujian'      => $data[1],
                    'singkat_ujian' => $data[2],
                ]);
            }
            $transRow = false;
        }
        fclose($csvData);

        // Import Data Honor Ujian
        $csvData = fopen(base_path('/database/seeders/csvs/exampayments.csv'), 'r');
        $transRow = true;
        while (($data = fgetcsv($csvData, 555, ',')) !== false) {
            if (!$transRow) {
                ExamPayment::create([
                    'nama'      => $data[0],
                    'jabatan_akademik'      => $data[1],
                    'pendidikan'      => $data[2],
                    'honor'      => $data[3],
                ]);
            }
            $transRow = false;
        }
        fclose($csvData);
    }
}
