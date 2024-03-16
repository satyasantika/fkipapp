<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewExamRegistration;
use App\DataTables\ViewExamRegistrationByDateDataTable;

class ReportController extends Controller
{
    public function showExamReport()
    {
        $lists = ViewExamRegistration::select('kode_laporan')->distinct()->get()->sortDesc();
        return view('reports.by-all',compact('lists'));
    }

    public function showExamReportByPeriode($periode)
    {
        $lists = ViewExamRegistration::where('kode_laporan',$periode)->select('tanggal_ujian')->distinct()->get()->sort();
        return view('reports.by-periode',compact('lists','periode'));
    }

    public function showExamReportByDate(ViewExamRegistrationByDateDataTable $dataTable, $date)
    {
        return $dataTable->with('date',$date)->render('reports.by-date',compact('date'));
    }
}
