@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">

                    {{ __('Selamat Datang di Aplikasi Laporan Ujian FKIP Universitas Siliwangi!') }}<br>
                    silakan periode laporan berikut
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th></th>
                                <th>periode laporan</th>
                                <th>total dibayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lists as $list)
                                <tr>
                                    <td><a href="{{ route('reports.date',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">view</a></td>
                                    <td>{{ $list->kode_laporan }}</td>
                                    <td></td>
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
