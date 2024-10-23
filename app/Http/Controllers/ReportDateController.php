<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ReportDate;
use Illuminate\Http\Request;
use App\DataTables\ViewReportDatesDataTable;

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
        return view('forms.reportdate',array_merge(
            [ 'reportdate' => $reportdate ],
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReportDate $reportdate)
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

    public function list(string $id)
    {
        //
    }
}
