<?php

namespace App\Filament\Resources\ExamRegistrationResource\Pages;

use App\Filament\Resources\ExamRegistrationResource;
use App\Models\ExamRegistration;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListExamRegistrations extends ListRecords
{
    protected static string $resource = ExamRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Registrasi Ujian')
                ->using(function (array $data): Model {
                    $student = Student::findOrFail($data['student_id']);

                    $record = ExamRegistration::updateOrCreate([
                        'departement_id' => $student->departement_id,
                        'student_id' => $data['student_id'],
                        'exam_type_id' => $data['exam_type_id'] ?? null,
                        'ujian_ke' => $data['ujian_ke'] ?? null,
                    ], [
                        'tanggal_ujian' => $data['tanggal_ujian'] ?? null,
                        'waktu_mulai' => $data['waktu_mulai'] ?? null,
                        'waktu_akhir' => $data['waktu_akhir'] ?? null,
                        'ruangan' => $data['ruangan'] ?? null,
                        'judul_penelitian' => $data['judul_penelitian'] ?? null,
                        'ipk' => $data['ipk'] ?? null,
                        'penguji1_id' => $student->penguji1_id,
                        'penguji2_id' => $student->penguji2_id,
                        'penguji3_id' => $student->penguji3_id,
                        'pembimbing1_id' => $student->pembimbing1_id,
                        'pembimbing2_id' => $student->pembimbing2_id,
                        'ketuapenguji_id' => $student->ketuapenguji_id,
                    ]);

                    if ($dateColumn = ExamRegistrationResource::examTypeDateColumn($data['exam_type_id'] ?? null)) {
                        $student->update([$dateColumn => $data['tanggal_ujian'] ?? null]);
                    }

                    return $record;
                }),
        ];
    }
}
