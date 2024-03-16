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
                                <th>periode laporan</th>
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
                                            $penguji = App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('pembimbing1_id')->count()
                                                        +App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('pembimbing2_id')->count()
                                                        +App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('penguji1_id')->count()
                                                        +App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('penguji2_id')->count()
                                                        +App\Models\ViewExamRegistration::where('tanggal_ujian',$list->tanggal_ujian)->whereNotNull('penguji3_id')->count();
                                        @endphp
                                        {{ $penguji }}
                                        <a href="{{ route('reports.date',$list->tanggal_ujian) }}" class="btn btn-sm btn-outline-primary">detail</a>
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
