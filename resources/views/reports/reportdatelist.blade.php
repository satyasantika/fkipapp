@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    Daftar Laporan Ujian pada Sesi Penarikan Tanggal {{ Carbon\Carbon::parse($tanggal)->isoFormat('LL') }}
                    <a href="{{ route('reportdates.index') }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>
                <div class="card-body table-responsive">
                    <a href="{{ route('reportdates.notreportedlist',$report_date_id) }}" class="btn btn-sm btn-success mb-2">+ data pelaporan</a>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif
                    {{ $dataTable->table()}}
                    @stack('body')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
