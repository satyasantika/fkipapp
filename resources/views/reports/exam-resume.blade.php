@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Rekap Penyelenggaraan Ujian') }}</div>

                <div class="card-body">
                    <div class="accordion" id="accordionExample">
                        @forelse (App\Models\ViewExamRegistration::where('departement_id',auth()->user()->departement_id)->select('kode_laporan')->distinct()->get()->sortDesc() as $list)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $list->kode_laporan }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $list->kode_laporan }}" aria-expanded="false" aria-controls="collapse{{ $list->kode_laporan }}">
                                @php
                                    $penguji = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->where('departement_id',auth()->user()->departement_id);
                                    $pembimbing1 = $penguji->whereNotNull('pembimbing1_id')->pluck('pembimbing1_id');
                                    $pembimbing2 = $penguji->whereNotNull('pembimbing2_id')->pluck('pembimbing2_id');
                                    $penguji1 = $penguji->whereNotNull('penguji1_id')->pluck('penguji1_id');
                                    $penguji2 = $penguji->whereNotNull('penguji2_id')->pluck('penguji2_id');
                                    $penguji3 = $penguji->whereNotNull('penguji3_id')->pluck('penguji3_id');
                                    $collection = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3);
                                @endphp
                                Bulan {{ Carbon\Carbon::createFromFormat('Y-m',$list->kode_laporan)->isoFormat('MMMM Y') }}&nbsp;
                                - Total Ujian:&nbsp; <span class="badge bg-primary">{{ $penguji->count() }}</span>&nbsp;
                                - Total Penguji:&nbsp; <span class="badge bg-dark">{{ $collection->unique()->values()->count() }}</span>&nbsp;
                                @if (App\Models\ViewExamRegistration::where('departement_id',auth()->user()->departement_id)->where('kode_laporan',$list->kode_laporan)->where('dilaporkan',0)->exists())
                                <span class="badge bg-danger">ada yang belum dilaporkan</span>
                                @endif
                            </button>
                            </h2>
                            <div id="collapse{{ $list->kode_laporan }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $list->kode_laporan }}" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Tanggal Ujian</th>
                                            @hasrole('jurusan')
                                            <th>Peserta Ujian</th>
                                            <th>Penguji</th>
                                            @else
                                            <th>total dibayar</th>
                                            @endhasrole
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse (App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)
                                                    ->where('departement_id',auth()->user()->departement_id)
                                                    ->select('tanggal_ujian')->distinct()->get()->sort(); as $list2)
                                            <tr>
                                                <td>
                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d',$list2->tanggal_ujian)->isoFormat('dddd, LL') }}
                                                    @if (App\Models\ViewExamRegistration::where([
                                                        'departement_id'=>auth()->user()->departement_id,
                                                        'tanggal_ujian'=>$list2->tanggal_ujian,
                                                        'report_date_id'=>NULL
                                                        ])->exists())
                                                    <br><span class="badge bg-danger">ada yang belum dibayar</span>
                                                    @endif

                                                </td>
                                                <td>
                                                    {{ \App\Models\ViewExamRegistration::where('tanggal_ujian',$list2->tanggal_ujian)->where('departement_id',auth()->user()->departement_id)->count() }}
                                                    <a href="{{ route('reports.by.date',$list2->tanggal_ujian) }}" class="btn btn-sm btn-outline-primary">detail ujian</a>
                                                </td>
                                                <td>
                                                    @php
                                                        $penguji = App\Models\ViewExamRegistration::where('tanggal_ujian',$list2->tanggal_ujian)->where('departement_id',auth()->user()->departement_id);
                                                        $pembimbing1 = $penguji->whereNotNull('pembimbing1_id')->pluck('pembimbing1_id');
                                                        $pembimbing2 = $penguji->whereNotNull('pembimbing2_id')->pluck('pembimbing2_id');
                                                        $penguji1 = $penguji->whereNotNull('penguji1_id')->pluck('penguji1_id');
                                                        $penguji2 = $penguji->whereNotNull('penguji2_id')->pluck('penguji2_id');
                                                        $penguji3 = $penguji->whereNotNull('penguji3_id')->pluck('penguji3_id');
                                                        $collection = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3);
                                                    @endphp
                                                    {{ $collection->unique()->values()->count() }}
                                                    <a href="{{ route('reports.by.examiner',$list2->tanggal_ujian) }}" class="btn btn-sm btn-outline-primary">detail penguji</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3">belum ada laporan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                        @empty
                            belum ada laporan
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
