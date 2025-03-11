<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ReportDate;
use App\Models\ExamPayment;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Models\ExamPaymentReport;
use App\Models\ViewExamRegistration;
use App\Models\ViewExamPaymentReport;
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
     * Update the specified resource in storage.
     */
    public function setReportDate(Request $request, ExamRegistration $examregistration)
    {
        $report_date_id = empty($examregistration->report_date_id) ? $request->report_date_id : $examregistration->report_date_id;

        $data = $request->all();
        $examregistration->fill($data)->save();
        $this->_reportStore($examregistration->id,$report_date_id);

        return redirect()->back();
    }

    private function _getCountOfExaminer($exam_type_id,$report_date_id,$guide_cek,$guide_order,$guide_id)
    {
        return ViewExamRegistration::where('exam_type_id',$exam_type_id)
                                    ->where('report_date_id',$report_date_id)
                                    ->where('dilaporkan',1)
                                    ->where($guide_cek,1)
                                    ->where($guide_order,$guide_id)
                                    ->count();
    }

    public function _reportStore($examregistration_id,$report_date_id)
    {
        $examregistration = ExamRegistration::find($examregistration_id);
        // $report_date_id = $examregistration->report_date_id;
        $exam_type_id = $examregistration->exam_type_id;

        foreach (['pembimbing1','pembimbing2','penguji1','penguji2','penguji3'] as $penguji) {
            $urutan_penguji = $penguji.'_id';
            $cek_penguji_dibayar = $penguji.'_dibayar';
            $banyak_menguji = 'banyak_'.$penguji;
            $id_penguji = $examregistration->$urutan_penguji;

            $pembimbing1 = $this->_getCountOfExaminer($exam_type_id,$report_date_id,'pembimbing1_dibayar','pembimbing1_id',$id_penguji);
            $pembimbing2 = $this->_getCountOfExaminer($exam_type_id,$report_date_id,'pembimbing2_dibayar','pembimbing2_id',$id_penguji);
            $penguji1 = $this->_getCountOfExaminer($exam_type_id,$report_date_id,'penguji1_dibayar','penguji1_id',$id_penguji);
            $penguji2 = $this->_getCountOfExaminer($exam_type_id,$report_date_id,'penguji2_dibayar','penguji2_id',$id_penguji);
            $penguji3 = $this->_getCountOfExaminer($exam_type_id,$report_date_id,'penguji3_dibayar','penguji3_id',$id_penguji);

            $semua = $pembimbing1 + $pembimbing2 + $penguji1 + $penguji2 + $penguji3;

            $data_tambahan = [];
            if ($exam_type_id == 3) {
                if ($penguji == 'pembimbing1') {
                    $data_tambahan['banyak_membimbing1'] = $pembimbing1;
                }
                if ($penguji == 'pembimbing2') {
                    $data_tambahan['banyak_membimbing2'] = $pembimbing2;
                }

                $data_tambahan['banyak_menguji_skripsi'] = $semua;

            } elseif ($exam_type_id == 1) {
                $data_tambahan['banyak_menguji_proposal'] = $semua;
            } else {
                $data_tambahan['banyak_menguji_seminar'] = $semua;
            }

            ExamPaymentReport::updateOrCreate([
                'report_date_id'=>$report_date_id,
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

            ReportDate::updateOrCreate([
                'id'=>$report_date_id,
            ],array_merge([
                'dibayar'=>ViewExamPaymentReport::where('report_date_id',$report_date_id)->sum('total_honor')
            ]));
        }
    }

}
