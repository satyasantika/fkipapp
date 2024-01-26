<?php

namespace App\Http\Controllers\Examination;

use App\Models\ExamDate;
use App\Models\ViewExamDate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\Examination\ViewExamDatesDataTable;

class DateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewExamDatesDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $examdate = new ViewExamDate();
        return view('examination.examdate-form',array_merge(
            [ 'examdate' => $examdate ],
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->tanggal_ujian);
        $data = [
            'departement_id'=> auth()->user()->departement_id,
            'tanggal_ujian'=> $request->tanggal_ujian,
            'kelompok_ujian'=> date('Ym',strtotime($request->tanggal_ujian)),
        ];
        ExamDate::create($data);
        return to_route('examdates.index')->with('success','data telah ditambahkan');
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
    public function edit(ExamDate $examdate)
    {
        return view('examination.examdate-form', array_merge(
            // $this->_dataSelection(),
            [
                'examdate'=> $examdate,
            ],
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamDate $examdate)
    {
        $data = $request->all();
        $data['kelompok_ujian'] = date('Ym',strtotime($request->tanggal_ujian));
        $examdate->fill($data)->save();

        return to_route('examdates.index')->with('success','data telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamDate $examdate)
    {
        $examdate->delete();
        return to_route('examdates.index')->with('warning','data telah dihapus');
    }

}
