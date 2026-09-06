<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ReportDate;
use App\Models\ExamPayment;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Models\ExamPaymentReport;
use App\Services\ExamPaymentReportService;
use App\DataTables\ViewReportDatesDataTable;
use App\DataTables\ViewExamReportedDataTable;
use App\DataTables\ViewExamNotReportedDataTable;
use App\DataTables\ViewExamSidangConfirmedDataTable;

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
     * Roster mahasiswa dengan data ujian sidang yang belum pernah dimasukkan
     * ke reported-list periode manapun (report_date_id null). $report_date_id
     * dipakai sebagai tautan "kembali" DAN sebagai periode tujuan saat tombol
     * aksi menambahkan data ke laporan.
     */
    public function sidangConfirmedList(ViewExamSidangConfirmedDataTable $dataTable, $report_date_id)
    {
        $tanggal = ReportDate::find($report_date_id)->tanggal;
        return $dataTable->with('report_date_id',$report_date_id)->render('reports.sidangconfirmedlist',compact('tanggal','report_date_id'));
    }

    /**
     * Menambahkan ujian sidang $examregistration beserta sempro/semhas
     * mahasiswa yang sama yang belum pernah dilaporkan ke periode manapun,
     * sekaligus ke periode $request->report_date_id (periode yang sedang
     * dibuka staf keuangan saat menekan tombol ini).
     */
    public function confirmSidangCascade(Request $request, ExamRegistration $examregistration)
    {
        $report_date_id = $request->report_date_id;

        if (empty($report_date_id)) {
            return redirect()->back()->with('warning','Periode laporan tujuan tidak ditemukan.');
        }

        $toAdd = ExamRegistration::where('student_id',$examregistration->student_id)
            ->whereIn('exam_type_id',[1,2,3])
            ->whereNull('report_date_id')
            ->get();

        foreach ($toAdd as $item) {
            $item->update([
                'report_date_id' => $report_date_id,
                'dilaporkan' => 1,
            ]);

            try {
                $this->_reportStore($item->id,$report_date_id);
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('warning',$e->getMessage());
            }
        }

        $message = $toAdd->isEmpty()
            ? 'Tidak ada data ujian untuk mahasiswa ini.'
            : 'Berhasil menambahkan '.$toAdd->count().' data ujian ke laporan.';

        return redirect()->back()->with('success',$message);
    }

        /**
     * Update the specified resource in storage.
     */
    public function setReportDate(Request $request, ExamRegistration $examregistration)
    {
        $report_date_id = empty($examregistration->report_date_id) ? $request->report_date_id : $examregistration->report_date_id;

        $data = $request->all();
        $examregistration->fill($data)->save();

        try {
            $this->_reportStore($examregistration->id,$report_date_id);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('warning',$e->getMessage());
        }

        return redirect()->back();
    }

    public function _reportStore($examregistration_id,$report_date_id)
    {
        app(ExamPaymentReportService::class)->store($examregistration_id, $report_date_id);
    }

}
