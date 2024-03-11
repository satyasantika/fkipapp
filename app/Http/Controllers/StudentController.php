<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Student;
use App\Models\Departement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\ViewStudentsDataTable;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewStudentsDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $student = new Student();
        return view('forms.student',array_merge(
            [ 'student' => $student ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        $data = $request->all();
        Student::create($data->all());
        return to_route('students.index')->with('success','student '.$name.' telah ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return view('forms.student',array_merge(
            [ 'student' => $student ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $name = strtoupper($student->name);
        $data = $request->all();
        $student->fill($data)->save();

        return to_route('students.index')->with('success','student '.$name.' telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $name = strtoupper($student->name);
        $student->delete();
        return to_route('students.index')->with('success','mahasiswa '.$name.' telah dihapus');
    }

    private function _dataSelection()
    {
        return [
            'departements' =>  Departement::all()->sort(),
            'lectures' =>  Lecture::select('id','name','departement_id')
                                    ->orderBy('name')
                                    ->get(),
        ];
    }

}
