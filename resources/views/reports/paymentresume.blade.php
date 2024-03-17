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
                                <th>total dibayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lists as $list)
                                <tr>
                                    <td>
                                        <a href="{{ route('reports.date',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">view</a>
                                    </td>
                                    <td>
                                        {{ Carbon\Carbon::createFromFormat('Y-m',$list->kode_laporan)->isoFormat('MMMM Y') }}
                                    </td>
                                    @php
                                        $dibayar = App\Models\ViewExamPaymentReport::where('kode_laporan',$list->kode_laporan)->sum('honor_dibayar');
                                        // dd((int)$dibayar);
                                    @endphp
                                    <td>
                                        {{ Laraindo\RupiahFormat::currency($dibayar) }}<br>
                                        <i>{{ Laraindo\RupiahFormat::terbilang($dibayar) }}</i>
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
