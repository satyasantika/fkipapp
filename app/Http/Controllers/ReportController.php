<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewExamRegistration;

class ReportController extends Controller
{
    public function showExamReport()
    {
        $lists = ViewExamRegistration::select('kode_laporan')->distinct()->get()->sort();
        return view('reports.periode',compact('lists'));
    }
}
