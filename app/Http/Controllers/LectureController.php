<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Departement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\ViewLecturesDataTable;

class LectureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewLecturesDataTable $dataTable)
    {
        // dd($dataTable);
        return $dataTable->render('layouts.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lecture = new Lecture();
        return view('forms.lecture',array_merge(
            [ 'lecture' => $lecture ],
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
        Lecture::create($data->all());
        return to_route('lectures.index')->with('success','lecture '.$name.' telah ditambahkan');
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
    public function edit(Lecture $lecture)
    {
        return view('forms.lecture',array_merge(
            [ 'lecture' => $lecture ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lecture $lecture)
    {
        $name = strtoupper($lecture->name);
        $data = $request->all();
        $lecture->fill($data)->save();

        return to_route('lectures.index')->with('success','lecture '.$name.' telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lecture $lecture)
    {
        $name = strtoupper($lecture->name);
        $lecture->delete();
        return to_route('lectures.index')->with('success','mahasiswa '.$name.' telah dihapus');
    }

    private function _dataSelection()
    {
        return [
            'departements' =>  Departement::all()->sort(),
            'jafungs' =>  ['Asisten Ahli','Lektor','Lektor Kepala','Guru Besar'],
            'golongans' =>  ['3b','3c','3d','4a','4b','4c','4d','4e'],
            'kualifikasis' =>  ['S2','S3'],
        ];
    }

}
