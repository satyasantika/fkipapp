<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ReportDate;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\DataTables\ViewReportDatesDataTable;
use App\DataTables\ViewExamReportedDataTable;
use App\DataTables\ViewExamNotReportedDataTable;

class ReportDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewReportDatesDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $reportdate = new ReportDate();
        return view('forms.reportdate',array_merge(
            [ 'reportdate' => $reportdate ],
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tanggal = Carbon::parse($request->tanggal)->isoFormat('LL');
        $data = $request->all();
        ReportDate::create($data);
        return to_route('reportdates.index')->with('success','penarikan laporan tanggal '.$tanggal.' telah ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportDate $reportdate)
    {
        $ada_laporan = ExamRegistration::where('report_date_id',$reportdate->id)->exists();
        return view('forms.reportdate',array_merge(
            [
                'reportdate' => $reportdate,
                'ada_laporan' => $ada_laporan,
            ],
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ada_laporan $reportdate)
    {
        $data = $request->all();
        $reportdate->fill($data)->save();

        return to_route('reportdates.index')->with('warning','penarikan laporan telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportDate $reportdate)
    {
        $tanggal = Carbon::parse($reportdate->tanggal)->isoFormat('LL');
        $reportdate->delete();
        return to_route('reportdates.index')->with('warning','penarikan laporan tanggal '.$tanggal.' telah dihapus');
    }

    public function reportedList(ViewExamReportedDataTable $dataTable, $report_date_id)
    {
        $tanggal = ReportDate::find($report_date_id)->tanggal;
        return $dataTable->with('report_date_id',$report_date_id)->render('reports.reportdatelist',compact('tanggal','report_date_id'));
    }

    public function notReportedList(ViewExamNotReportedDataTable $dataTable, $report_date_id)
    {
        $tanggal = ReportDate::find($report_date_id)->tanggal;
        return $dataTable->with('report_date_id',$report_date_id)->render('reports.notreportdatelist',compact('tanggal','report_date_id'));
    }

        /**
     * Update the specified resource in storage.
     */
    public function setReportDate(Request $request, ExamRegistration $examregistration)
    {
        // TO FIX
        // if ((int)$request->date_report_id>0) {
        //     $pesan = 'ditambahkan pada';
        //     $alert = 'success';
        // } else {
        //     $pesan = 'dicabut dari';
        //     $alert = 'warning';
        // }

        $data = $request->all();
        $examregistration->fill($data)->save();

        // return redirect()->back()->with($alert,'data ujian telah '.$pesan.' sesi penarikan laporan');
        return redirect()->back();
    }

}
