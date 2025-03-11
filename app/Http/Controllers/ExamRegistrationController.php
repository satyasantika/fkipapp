<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Student;
use App\Models\ExamType;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Http\Controllers\Controller;
use App\Models\ViewExamRegistration;
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
        $ujian = ExamType::find($request->exam_type_id)->nama_ujian;

        ExamRegistration::updateOrCreate([
            'departement_id'=>$request->departement_id,
            'student_id'=>$request->student_id,
            'exam_type_id'=>$request->exam_type_id,
            'ujian_ke'=>$request->ujian_ke,
        ],[
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

        $student->update([
            $this->_examType($request->exam_type_id)=>$request->tanggal_ujian,
        ]);
        return to_route('registrations.show.student',$student->id)->with('success','data '.$ujian.' untuk mahasiswa '.$name.' telah ditambahkan');
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
    public function edit(ExamRegistration $registration)
    {
        return view('forms.examregistration',array_merge(
            [
                'student' => Student::find($registration->student_id),
                'examregistration' => $registration,
            ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamRegistration $registration)
    {
        $student= Student::find($registration->student_id);
        $ujian = $registration->exam_type->nama_ujian;
        $name = strtoupper($student->nama);
        $data = $request->all();

        $data['penguji1_dibayar'] = $request->penguji1_dibayar=='on' ? 1 : 0;
        $data['penguji2_dibayar'] = $request->penguji2_dibayar=='on' ? 1 : 0;
        $data['penguji3_dibayar'] = $request->penguji3_dibayar=='on' ? 1 : 0;
        $data['pembimbing1_dibayar'] = $request->pembimbing1_dibayar=='on' ? 1 : 0;
        $data['pembimbing2_dibayar'] = $request->pembimbing2_dibayar=='on' ? 1 : 0;
        $registration->fill($data)->save();

        $student->update([
            'penguji1_id'=>$registration->penguji1_id,
            'penguji2_id'=>$registration->penguji2_id,
            'penguji3_id'=>$registration->penguji3_id,
            'pembimbing1_id'=>$registration->pembimbing1_id,
            'pembimbing2_id'=>$registration->pembimbing2_id,
            'ketuapenguji_id'=>$registration->ketuapenguji_id,
            $this->_examType($registration->exam_type_id)=>$registration->tanggal_ujian,
        ]);

        return to_route('registrations.edit',$registration)->with('success','data '.$ujian.' untuk mahasiswa '.$name.' telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamRegistration $registration)
    {
        $student= Student::find($registration->student_id);
        $ujian = $registration->exam_type->nama_ujian;
        $name = strtoupper($student->nama);
        $student->update([
            $this->_examType($registration->exam_type_id)=>NULL,
        ]);

        $registration->delete();
        return to_route('registrations.show',$student->id)->with('warning','data '.$ujian.' untuk '.$name.' telah dihapus');
    }

    public function createByStudent($student_id)
    {
        $examregistration = new ExamRegistration();
        return view('forms.examregistration',array_merge(
            [
                'student' => Student::find($student_id),
                'examregistration' => $examregistration,
            ],
            $this->_dataSelection(),
        ));
    }

    public function showByStudent($student_id)
    {
        $examregistrations = ViewExamRegistration::where('student_id',$student_id)->orderBy('tanggal_ujian')->get();
        return view('reports.examregistration',compact('examregistrations','student_id'));
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

    private function _examType($type)
    {
        if ($type == 1) {
            return 'tanggal_proposal';
        }
        if ($type == 2) {
            return 'tanggal_seminar';
        }
        if ($type == 3) {
            return 'tanggal_skripsi';
        }
    }
}
