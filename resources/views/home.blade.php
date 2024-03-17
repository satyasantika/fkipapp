@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('Selamat Datang di Aplikasi Laporan Ujian FKIP Universitas Siliwangi!') }}<br>
                    <p>silakan pilih menu berikut</p>
                    @includeWhen(auth()->user()->hasRole('admin'),'dashboards.admin')
                    @includeWhen(auth()->user()->hasRole('jurusan'),'dashboards.departement')
                    @includeWhen(auth()->user()->hasRole('keuangan'),'dashboards.financial')
                    {{-- @includeWhen(auth()->user()->role('dekanat'),'dashboards.dekanat') --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
