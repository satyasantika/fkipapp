<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use Illuminate\Http\Request;
use App\Models\ViewExamRegistration;
use App\DataTables\ViewExamRegistrationByDateDataTable;

class ReportController extends Controller
{
    public function showExamReport()
    {
        // $lists = ViewExamRegistration::where('departement_id',auth()->user()->departement_id)->select('kode_laporan')->distinct()->get()->sortDesc();
        return view('reports.exam-resume');
    }

    public function showExamReportByPeriode($periode)
    {
        $lists = ViewExamRegistration::where('kode_laporan',$periode)
                        ->where('departement_id',auth()->user()->departement_id)
                        ->select('tanggal_ujian')->distinct()->get()->sort();
        return view('reports.by-periode',compact('lists','periode'));
    }

    public function showExamReportByDate(ViewExamRegistrationByDateDataTable $dataTable, $date)
    {
        return $dataTable->with('date',$date)->render('reports.by-date',compact('date'));
    }

    public function showExamReportByExaminer($date)
    {
        $departement_id = auth()->user()->departement_id;
        $penguji = ViewExamRegistration::where('tanggal_ujian',$date)->where('departement_id',$departement_id);
        $pembimbing1 = $penguji->whereNotNull('pembimbing1_id')->pluck('pembimbing1_id');
        $pembimbing2 = $penguji->whereNotNull('pembimbing2_id')->pluck('pembimbing2_id');
        $penguji1 = $penguji->whereNotNull('penguji1_id')->pluck('penguji1_id');
        $penguji2 = $penguji->whereNotNull('penguji2_id')->pluck('penguji2_id');
        $penguji3 = $penguji->whereNotNull('penguji3_id')->pluck('penguji3_id');
        $examiner_ids = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3)->unique()->values()->all();

        $examiners = Lecture::select('id','nama')->whereIn('id',$examiner_ids)->orderBy('nama')->get();
        return view('reports.by-examiner',compact('date','examiners'));
    }
}
