<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Lecture;
use App\Models\ReportDate;
use App\Models\ExamPayment;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Models\ExamPaymentReport;
use App\Models\ViewExamRegistration;
use App\Models\ViewExamPaymentReport;
use App\DataTables\ViewExamPaymentReportsDataTable;

class ExamPaymentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lists = ExamPaymentReport::select('report_date_id')->distinct()->get()->sortDesc();
        return view('reports.paymentresume',compact('lists'));
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
        $this->_reportStore($request->examregistration_id);
        $name = strtoupper(ExamRegistration::find($request->examregistration_id)->student->nama);


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
    public function edit(ViewExamPaymentReport $paymentreport)
    {
        return view('forms.exampaymentreport',array_merge(
            [
                'paymentreport' => $paymentreport,
            ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamPaymentReport $paymentreport)
    {
        $data = $request->all();
        $data['status'] = $request->pns ? 1 : 0;
        $data['honor_pembimbing'] =  ExamPayment::where('jabatan_akademik',$request->jabatan_akademik)->where('pendidikan',$request->pendidikan)->first()->honor;
        $paymentreport->fill($data)->save();

        $pass = [
            'pns' => $paymentreport->status,
            'report_date_id' => $paymentreport->report_date_id,
        ];

        return to_route('reports.section',$pass)->with('success','data penguji '.$paymentreport->dosen.' telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamPaymentReport $paymentreport)
    {
        $name = strtoupper($paymentreport->dosen);
        $pass = [
            'pns' => $paymentreport->status,
            'report_date_id' => $paymentreport->report_date_id,
        ];
        $paymentreport->delete();
        return to_route('reports.section',$pass)->with('warning','data penguji '.$name.' telah dihapus');
    }

    public function reportBySection(ViewExamPaymentReportsDataTable $dataTable, $pns, $report_date_id)
    {
        return $dataTable->with([
            'pns'=>$pns,
            'report_date_id'=>$report_date_id,
            ])->render('reports.exampaymentreport',compact('report_date_id'));
    }

    public function reportExaminerByPeriode($report_date_id)
    {
        $pembimbing1 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing1_id');
        $pembimbing2 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing2_id');
        $penguji1 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji1_id');
        $penguji2 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji2_id');
        $penguji3 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji3_id');
        $penguji = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3)->unique()->values()->all();
        $examiners = Lecture::whereIn('id',$penguji)->orderBy('nama')->get();
        return view('reports.exam-by-periode',compact('report_date_id','examiners'));
    }

    public function reportExaminerByDate($date)
    {
        $pembimbing1 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing1_id');
        $pembimbing2 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing2_id');
        $penguji1 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji1_id');
        $penguji2 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji2_id');
        $penguji3 = ViewExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji3_id');
        $penguji = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3)->unique()->values()->all();
        $examiners = Lecture::whereIn('id',$penguji)->orderBy('nama')->get();
        return view('reports.exam-by-periode',compact('report_date_id','examiners'));
    }

    public function reportFreshByPeriode($periode)
    {
        $tanggal = ViewExamRegistration::where('report_date_id',$periode)->pluck('tanggal_ujian');
        $dates = collect($tanggal)->unique()->sort()->values()->all();
        $total = collect($tanggal)->unique()->count();
        return view('reports.fresh-by-date',compact('periode','dates','total'));
    }

    // report massal
    public function massReportByDate($periode,$date)
    {
        $tanggal = Carbon::createFromFormat('Y-m-d',$date)->isoFormat('dddd, LL');
        $examregistrations = ViewExamRegistration::where('tanggal_ujian',$date)->where('report_date_id',$periode)->pluck('id');
        // dd($examregistrations);
        foreach ($examregistrations as $examregistration) {
            $this->_reportStore($examregistration);
        }

        return redirect()->back()->with('success','data ujian tanggal '.$tanggal.' telah disegarkan');
    }

    // banyaknya membimbing/menguji pada ujian skripsi/proposal/seminar
    private function _getCountOfExaminer($exam_type_id,$report_date_id,$guide_cek,$guide_order,$guide_id)
    {
        return ViewExamRegistration::where('exam_type_id',$exam_type_id)
                                    ->where('report_date_id',$report_date_id)
                                    ->where('dilaporkan',1)
                                    ->where($guide_cek,1)
                                    ->where($guide_order,$guide_id)
                                    ->count();
    }

    private function _dataSelection()
    {
        return [
            'jabatan_akademiks' =>  ['Asisten Ahli','Lektor','Lektor Kepala','Guru Besar'],
            'golongans' =>  ['3','4'],
            'pendidikans' =>  ['S2','S3'],
        ];
    }

    public function _reportStore($examregistration_id)
    {
        $examregistration = ExamRegistration::find($examregistration_id);
        $report_date_id = $examregistration->report_date_id;
        $exam_type_id = $examregistration->exam_type_id;

        $examregistration->update([
            'dilaporkan'=>1,
        ]);

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
