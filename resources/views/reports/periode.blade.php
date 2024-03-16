@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th></th>
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
                                    <td><a href="{{ route('reports.date',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">view</a></td>
                                    <td>{{ $list->kode_laporan }}</td>
                                    @hasrole('jurusan')
                                    <td>
                                        {{ \App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->count() }}
                                        <a href="{{ route('reports.date',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">detail</a>
                                    </td>
                                    <td>
                                        @php
                                            $penguji = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('pembimbing1_id')->count()
                                                        +App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('pembimbing2_id')->count()
                                                        +App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('penguji1_id')->count()
                                                        +App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('penguji2_id')->count()
                                                        +App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->whereNotNull('penguji3_id')->count();
                                        @endphp
                                        {{ $penguji }}
                                        <a href="{{ route('reports.date',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">detail</a>
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
