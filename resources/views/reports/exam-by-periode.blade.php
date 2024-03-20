@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    {{ __('Rekap Penguji pada bulan ').Carbon\Carbon::createFromFormat('Y-m',$kode_laporan)->isoFormat('MMMM Y') }}
                    <a href="{{ route('paymentreports.index') }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>

                <div class="card-body">
                    <h4>
                        TOTAL: {{ $examiners->count() }} Penguji
                    </h4>
                    <hr>
                    <table class="table table-hover table-responsive table-sm">
                        <tbody>
                            @foreach ($examiners as $examiner)
                            @php
                                $examregistrations = App\Models\ViewExamRegistration::where('kode_laporan',$kode_laporan)
                                                    ->where(function($query) use ($examiner){
                                                        $query->where('pembimbing1_id',$examiner->id)
                                                            ->orWhere('pembimbing2_id',$examiner->id)
                                                            ->orWhere('penguji1_id',$examiner->id)
                                                            ->orWhere('penguji2_id',$examiner->id)
                                                            ->orWhere('penguji3_id',$examiner->id);
                                                    });

                                $sidang = App\Models\ViewExamRegistration::where('kode_laporan',$kode_laporan)
                                                    ->where(function($query) use ($examiner){
                                                        $query->where('pembimbing1_id',$examiner->id)
                                                            ->orWhere('pembimbing2_id',$examiner->id)
                                                            ->orWhere('penguji1_id',$examiner->id)
                                                            ->orWhere('penguji2_id',$examiner->id)
                                                            ->orWhere('penguji3_id',$examiner->id);
                                                    })->where('exam_type_id',3)->get()->count();
                                $proposal = App\Models\ViewExamRegistration::where('kode_laporan',$kode_laporan)
                                                    ->where(function($query) use ($examiner){
                                                        $query->where('pembimbing1_id',$examiner->id)
                                                            ->orWhere('pembimbing2_id',$examiner->id)
                                                            ->orWhere('penguji1_id',$examiner->id)
                                                            ->orWhere('penguji2_id',$examiner->id)
                                                            ->orWhere('penguji3_id',$examiner->id);
                                                    })->where('exam_type_id',1)->get()->count();
                                $seminar = App\Models\ViewExamRegistration::where('kode_laporan',$kode_laporan)
                                                    ->where(function($query) use ($examiner){
                                                        $query->where('pembimbing1_id',$examiner->id)
                                                            ->orWhere('pembimbing2_id',$examiner->id)
                                                            ->orWhere('penguji1_id',$examiner->id)
                                                            ->orWhere('penguji2_id',$examiner->id)
                                                            ->orWhere('penguji3_id',$examiner->id);
                                                    })->where('exam_type_id',2)->get()->count();
                                $lecture = App\Models\Lecture::find($examiner->id);
                            @endphp
                                <tr>
                                    <td>{{ $lecture->nama }} ({{ $lecture->departement_id }})</td>
                                    <td>sidang: &nbsp;<span class="text-primary h3">{{ $sidang }}</span></td>
                                    <td>proposal: &nbsp;<span class="text-primary h3">{{ $proposal }}</td>
                                    <td>seminar: &nbsp;<span class="text-primary h3">{{ $seminar }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
