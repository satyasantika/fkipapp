<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Student;
use App\Models\ExamType;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Http\Controllers\Controller;
use App\DataTables\ViewExamRegistrationsDataTable;

class ExamRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewExamRegistrationsDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $student = Student::find($request->student_id);
        $name = strtoupper($student->name);
        $data = $request->all();
        ExamRegistration::create([
            'departement_id'=>$request->departement_id,
            'student_id'=>$request->student_id,
            'exam_type_id'=>$request->exam_type_id,
            'ujian_ke'=>$request->ujian_ke,
            'tanggal_ujian'=>$request->tanggal_ujian,
            'waktu_mulai'=>$request->waktu_mulai,
            'waktu_akhir'=>$request->waktu_akhir,
            'ruangan'=>$request->ruangan,
            'judul_penelitian'=>$request->judul_penelitian,
            'ipk'=>$request->ipk,
            'penguji1_id'=>$student->penguji1_id,
            'penguji2_id'=>$student->penguji2_id,
            'penguji3_id'=>$student->penguji3_id,
            'pembimbing1_id'=>$student->pembimbing1_id,
            'pembimbing2_id'=>$student->pembimbing2_id,
            'ketuapenguji_id'=>$student->ketuapenguji_id,
        ]);
        return to_route('registrations.show',$student->id)->with('success','ujian untuk '.$name.' telah ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(ViewExamRegistrationsDataTable $dataTable, $student_id)
    {
        return $dataTable->with('student_id',$student_id)->render('layouts.setting');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createByStudent($student_id)
    {
        $registration = new ExamRegistration();
        return view('forms.examregistration',array_merge(
            [
                'student' => Student::find($student_id),
                'registration' => $registration,
            ],
            $this->_dataSelection(),
        ));
    }

    private function _dataSelection()
    {
        if (auth()->user()->hasRole('jurusan')) {
            $lectures = Lecture::select('id','nama','departement_id')
                                        ->where('departement_id',auth()->user()->departement_id)
                                        ->orderBy('nama')
                                        ->get();
        } else {
            $lectures = Lecture::select('id','nama','departement_id')
                                        ->orderBy('nama')
                                        ->get();
        }

        return [
            'lectures' =>  $lectures,
            'exam_types' =>  ExamType::select('id','nama_ujian')->get(),
        ];
    }
}
