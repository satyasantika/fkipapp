@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('Rekap Penyelenggaraan Ujian pada periode ').$periode }}
                    <a href="{{ route('reports.by.departement') }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>

                <div class="card-body">

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
                            @forelse ($lists as $list)
                                <tr>
                                    <td>{{ Carbon\Carbon::createFromFormat('Y-m-d',$list->tanggal_ujian)->isoFormat('dddd, LL') }}</td>
                                    <td>
                                        {{ \App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->count() }}
                                        <a href="{{ route('reports.by.date',$list->tanggal_ujian) }}" class="btn btn-sm btn-outline-primary">detail</a>
                                    </td>
                                    <td>
                                        @php
                                            $pembimbing1 = App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('pembimbing1_id')->pluck('pembimbing1_id');
                                            $pembimbing2 = App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('pembimbing2_id')->pluck('pembimbing2_id');
                                            $penguji1 = App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('penguji1_id')->pluck('penguji1_id');
                                            $penguji2 = App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('penguji2_id')->pluck('penguji2_id');
                                            $penguji3 = App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('penguji3_id')->pluck('penguji3_id');
                                            $collection = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3);
                                        @endphp
                                        {{ $collection->unique()->values()->count() }}
                                        <a href="{{ route('reports.by.examiner',$list->tanggal_ujian) }}" class="btn btn-sm btn-outline-primary">detail</a>
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
    </div>
</div>
@endsection
