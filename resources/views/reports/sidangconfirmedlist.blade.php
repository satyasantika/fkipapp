@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    Data Pasti Sidang &mdash; Mahasiswa yang Ujian Sidangnya Belum Dilaporkan
                    <a href="{{ route('reportdates.reportedlist',$report_date_id) }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>
                <div class="card-body table-responsive">
                    <p class="text-muted small">
                        Daftar berikut berisi mahasiswa yang ujian sidangnya belum pernah dimasukkan ke
                        laporan periode manapun. Tombol pada kolom action menambahkan sidang tersebut
                        &mdash; beserta sempro/semhas mahasiswa yang sama, kalau tersedia dan juga belum
                        pernah dilaporkan &mdash; sekaligus ke periode laporan ini.
                    </p>
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
