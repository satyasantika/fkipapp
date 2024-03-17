@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Rekap Penyelenggaraan Ujian') }}</div>

                <div class="card-body">

                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>periode laporan</th>
                                @hasrole('jurusan')
                                <th>Banyak Ujian</th>
                                <th>Penguji</th>
                                @else
                                <th>total dibayar</th>
                                @endhasrole
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lists as $list)
                                <tr>
                                    <td>{{ $list->kode_laporan }}</td>
                                    @hasrole('jurusan')
                                    <td>
                                        {{ \App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->count() }}
                                        <a href="{{ route('reports.by.periode',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">detail</a>
                                    </td>
                                    <td>
                                        @php
                                            $pembimbing1 = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('pembimbing1_id')->pluck('pembimbing1_id');
                                            $pembimbing2 = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('pembimbing2_id')->pluck('pembimbing2_id');
                                            $penguji1 = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('penguji1_id')->pluck('penguji1_id');
                                            $penguji2 = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('penguji2_id')->pluck('penguji2_id');
                                            $penguji3 = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('penguji3_id')->pluck('penguji3_id');
                                            $collection = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3);
                                        @endphp
                                        {{ $collection->unique()->values()->count() }}
                                        {{-- <a href="{{ route('reports.date',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">detail</a> --}}
                                    </td>
                                    @else
                                    <td></td>
                                    @endhasrole
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