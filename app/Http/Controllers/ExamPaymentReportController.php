<?php

namespace App\Http\Controllers;

use App\Models\ExamPayment;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Models\ViewExamRegistration;
use App\Models\ExamPaymentReport;
use App\DataTables\ViewExamPaymentReportsDataTable;

class ExamPaymentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lists = ExamPaymentReport::select('kode_laporan')->distinct()->get()->sort();
        return view('reports.periode',compact('lists'));
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
        $kode_laporan = substr($examregistration->tanggal_ujian,0,7);
        $exam_type_id = $examregistration->exam_type_id;

        $examregistration->update([
            'dilaporkan'=>1,
        ]);

        foreach (['pembimbing1','pembimbing2','penguji1','penguji2','penguji3'] as $penguji) {
            $urutan_penguji = $penguji.'_id';
            $cek_penguji_dibayar = $penguji.'_dibayar';
            $banyak_menguji = 'banyak_'.$penguji;
            $id_penguji = $examregistration->$urutan_penguji;
            $cek_penguji = $examregistration->$cek_penguji_dibayar;
            $menguji = $this->_getCountOfExaminer($exam_type_id,$kode_laporan,$cek_penguji_dibayar,$urutan_penguji,$id_penguji);

            $data_tambahan = [];
            if ($exam_type_id == 3) {
                if ($penguji == 'pembimbing1') {
                    $data_tambahan['banyak_membimbing1']=$menguji;
                }
                if ($penguji == 'pembimbing2') {
                    $data_tambahan['banyak_membimbing2']=$menguji;
                }
                $data_tambahan['banyak_menguji_skripsi']=$menguji;
            } elseif ($exam_type_id == 1) {
                $data_tambahan['banyak_menguji_proposal']=$menguji;
                // dd($menguji);
            } else {
                $data_tambahan['banyak_menguji_seminar']=$menguji;
            }

            ExamPaymentReport::updateOrCreate([
                'kode_laporan'=>$kode_laporan,
                'lecture_id'=>$id_penguji,
            ],array_merge([
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
            ],$data_tambahan));
        }

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

    public function reportByDate(ViewExamPaymentReportsDataTable $dataTable, $date)
    {
        return $dataTable->with('date',$date)->render('layouts.setting');
    }

    // banyaknya membimbing/menguji pada ujian skripsi/proposal/seminar
    private function _getCountOfExaminer($exam_type_id,$kode_laporan,$guide_cek,$guide_order,$guide_id)
    {
        return ViewExamRegistration::where('exam_type_id',$exam_type_id)
                                    ->where('kode_laporan',$kode_laporan)
                                    ->where('dilaporkan',1)
                                    ->where($guide_cek,1)
                                    ->where($guide_order,$guide_id)
                                    ->count();
    }
}