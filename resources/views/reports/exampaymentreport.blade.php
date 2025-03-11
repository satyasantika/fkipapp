@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    Rekap Laporan Bulan {{ App\Models\ReportDate::find($report_date_id)->tanggal }} untuk Kelompok ({{ request()->segment(3) == 1 ? 'ASN' : 'non ASN' }})
                    <a href="{{ route('reports.fresh.periode',$report_date_id) }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>
                <div class="card-body">
                    <a href="{{ route('reports.empty-zero') }}" class="btn btn-sm btn-warning float-end">kosongkan tanpa honor</a>
                </div>
                <div class="card-body table-responsive">
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
