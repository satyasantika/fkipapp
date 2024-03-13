<?php

namespace App\Http\Controllers;

use App\Models\ExamPayment;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Models\ExamPaymentReport;
use App\DataTables\ViewExamPaymentReportsDataTable;

class ExamPaymentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewExamPaymentReportsDataTable $dataTable)
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
        $examregistration = ExamRegistration::find($request->examregistration_id);
        $name = strtoupper($examregistration->student->nama);
        // dd($examregistration);

        foreach (['pembimbing1','pembimbing2','penguji1','penguji2','penguji3'] as $penguji) {
            $id_penguji = $penguji.'_id';
            ExamPaymentReport::updateOrCreate([
                'kode_laporan'=>substr($examregistration->tanggal_ujian,0,7),
                'lecture_id'=>$examregistration->$id_penguji,
            ],[
                'status'=>$examregistration->$penguji->pns ? 1 : 0,
                'golongan'=>substr($examregistration->$penguji->golongan,0,1),
                'npwp'=>$examregistration->$penguji->npwp,
                'rekening'=>$examregistration->$penguji->rekening,
                'jabatan_akademik'=>$examregistration->$penguji->jabatan_akademik,
                'pendidikan'=>$examregistration->$penguji->pendidikan,
                'honor_pembimbing'=>ExamPayment::where('jabatan_akademik',$examregistration->$penguji->jabatan_akademik)->where('pendidikan',$examregistration->$penguji->pendidikan)->first()->honor,
                'honor_penguji_skripsi'=>ExamPayment::find(3)->honor,
                'honor_penguji_proposal'=>ExamPayment::find(1)->honor,
                'honor_penguji_seminar'=>ExamPayment::find(2)->honor,
            ]);
        }
        $examregistration->update([
            'dilaporkan'=>1,
        ]);

        // sempro
        // if ($examregistration->exam_type_id == 1) {
        //     if ($examregistration->pembimbing1_dibayar) {
        //     }
        // }
        // if ($examregistration->pembimbing1_dibayar) {
        //     ExamRegistration::where('pembimbing1_id',$examregistration->pembimbing1_id)->count();
        //     if ($examregistration->exam_type_id == 3) {
        //         ExamPaymentReport::updateOrCreate([
        //             'kode_laporan'=>substr($examregistration->tanggal_ujian,0,7),
        //             'lecture_id'=>$penguji,
        //         ],[
        //             'banyak_membimbing1'=>$exampaymentreport->banyak_membimbing1 + ($examregistration->pembimbing1_dibayar ? 1 : 0),
        //         ]);
        //     } else {
        //         continue;
        //     }
        // } else {
        //     # code...
        // }
        // semhas
        // sidang


        // foreach (['pembimbing1','pembimbing2','penguji1','penguji2','penguji3'] as $penguji) {

        //     if ($examregistration->$penguji.'_dibayar') {
        //         ExamRegistration::where($penguji.'_id',$examregistration->$penguji.'_id')->count();
        //         if ($examregistration->exam_type_id == 3) {
        //             ExamPaymentReport::updateOrCreate([
        //                 'kode_laporan'=>substr($examregistration->tanggal_ujian,0,7),
        //                 'lecture_id'=>$penguji,
        //             ],[
        //                 'banyak_membimbing1'=>$exampaymentreport->banyak_membimbing1 + ($examregistration->pembimbing1_dibayar ? 1 : 0),
        //             ]);
        //         } else {
        //             continue;
        //         }
        //     } else {
        //         # code...
        //     }


        //     $exampaymentreport = ExamPaymentReport::where([
        //         'lecture_id'=>$penguji,
        //         'kode_laporan'=>substr($examregistration->tanggal_ujian,0,7),
        //         ])->first();
        // }

        // $lecture->update([
        //     $this->_examType($request->exam_type_id)=>$request->tanggal_ujian,
        // ]);
        return back()->with('success','data laporan para penguji untuk mahasiswa '.$name.' telah ditambahkan');
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
}
