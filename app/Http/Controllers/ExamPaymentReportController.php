<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Lecture;
use App\Models\ReportDate;
use App\Models\ExamPayment;
use Illuminate\Http\Request;
use App\Models\ExamRegistration;
use App\Models\ExamPaymentReport;
use App\Services\ExamPaymentReportService;
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
        try {
            $this->_reportStore($request->examregistration_id);
        } catch (\RuntimeException $e) {
            return back()->with('warning',$e->getMessage());
        }

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
    public function edit(ExamPaymentReport $paymentreport)
    {
        $paymentreport->load('lecture');

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
        $examPayment = ExamPayment::where('jabatan_akademik',$request->jabatan_akademik)->where('pendidikan',$request->pendidikan)->first();

        if (!$examPayment) {
            return back()->with('warning','Data honor untuk jabatan akademik "'.$request->jabatan_akademik.'" dan pendidikan "'.$request->pendidikan.'" belum diatur di data honor ujian.');
        }

        $data = $request->all();
        $data['status'] = $request->pns ? 1 : 0;
        $data['honor_pembimbing'] = $examPayment->honor;
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

    public function emptyZeroHonor(){
        $paymentreport_ids = ExamPaymentReport::all()
            ->filter(fn ($paymentreport) => $paymentreport->honor_dibayar == 0)
            ->pluck('id');
        ExamPaymentReport::destroy($paymentreport_ids);
        return redirect()->back();
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
        $pembimbing1 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing1_id');
        $pembimbing2 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing2_id');
        $penguji1 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji1_id');
        $penguji2 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji2_id');
        $penguji3 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji3_id');
        $penguji = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3)->unique()->values()->all();
        $examiners = Lecture::whereIn('id',$penguji)->orderBy('nama')->get();
        return view('reports.exam-by-periode',compact('report_date_id','examiners'));
    }

    public function reportExaminerByDate($date)
    {
        $pembimbing1 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing1_id');
        $pembimbing2 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('pembimbing2_id');
        $penguji1 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji1_id');
        $penguji2 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji2_id');
        $penguji3 = ExamRegistration::where('report_date_id',$report_date_id)->pluck('penguji3_id');
        $penguji = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3)->unique()->values()->all();
        $examiners = Lecture::whereIn('id',$penguji)->orderBy('nama')->get();
        return view('reports.exam-by-periode',compact('report_date_id','examiners'));
    }

    public function reportFreshByPeriode($periode)
    {
        $tanggal = ExamRegistration::where('report_date_id',$periode)->pluck('tanggal_ujian');
        $dates = collect($tanggal)->unique()->sort()->values()->all();
        $total = collect($tanggal)->unique()->count();
        return view('reports.fresh-by-date',compact('periode','dates','total'));
    }

    // report massal
    public function massReportByDate($periode,$date)
    {
        $tanggal = Carbon::createFromFormat('Y-m-d',$date)->isoFormat('dddd, LL');
        $examregistrations = ExamRegistration::where('tanggal_ujian',$date)->where('report_date_id',$periode)->pluck('id');
        // dd($examregistrations);
        foreach ($examregistrations as $examregistration) {
            try {
                $this->_reportStore($examregistration);
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('warning',$e->getMessage());
            }
        }

        return redirect()->back()->with('success','data ujian tanggal '.$tanggal.' telah disegarkan');
    }

    private function _dataSelection()
    {
        return [
            'jabatan_akademiks' =>  ['Asisten Ahli','Lektor','Lektor Kepala','Guru Besar'],
            'golongans' =>  ['3','4'],
            'pendidikans' =>  ['S2','S3'],
        ];
    }

    /**
     * Beda dengan ReportDateController::_reportStore() (dipakai alur
     * cabut-laporan yang justru men-set dilaporkan=0 sendiri sebelum
     * memanggil), caller-caller method ini (store(), massReportByDate())
     * memang bermaksud "tandai sudah dilaporkan" - jadi paksa
     * dilaporkan=1 di sini sebelum mendelegasikan ke service bersama.
     */
    public function _reportStore($examregistration_id)
    {
        ExamRegistration::find($examregistration_id)->update(['dilaporkan' => 1]);
        app(ExamPaymentReportService::class)->store($examregistration_id);
    }

}
