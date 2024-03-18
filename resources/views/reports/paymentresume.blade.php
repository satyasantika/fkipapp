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
                                <th></th>
                                <th>Bulan laporan</th>
                                <th>total ASN dibayar</th>
                                <th>total nonASN dibayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lists as $list)
                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        {{ Carbon\Carbon::createFromFormat('Y-m',$list->kode_laporan)->isoFormat('MMMM Y') }}
                                    </td>
                                    @php
                                        $bayar_pns = App\Models\ViewExamPaymentReport::where('kode_laporan',$list->kode_laporan)->where('status',1)->sum('honor_dibayar');
                                        $bayar_nonpns = App\Models\ViewExamPaymentReport::where('kode_laporan',$list->kode_laporan)->where('status',0)->sum('honor_dibayar');
                                        // dd((int)$bayar_pns);
                                        @endphp
                                    <td>
                                        {{ Laraindo\RupiahFormat::currency($bayar_pns) }}
                                        <a href="{{ route('reports.date',['kode_laporan'=>$list->kode_laporan,'pns'=>1]) }}" class="btn btn-sm btn-outline-primary">detail</a><br>
                                        <i>{{ Laraindo\RupiahFormat::terbilang($bayar_pns) }}</i>
                                    </td>
                                    <td>
                                        {{ Laraindo\RupiahFormat::currency($bayar_nonpns) }}
                                        <a href="{{ route('reports.date',['kode_laporan'=>$list->kode_laporan,'pns'=>0]) }}" class="btn btn-sm btn-outline-primary">detail</a><br>
                                        <i>{{ Laraindo\RupiahFormat::terbilang($bayar_nonpns) }}</i>
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
