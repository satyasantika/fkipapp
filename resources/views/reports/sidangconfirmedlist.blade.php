@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    Data Pasti Sidang &mdash; Mahasiswa yang Ujian Sidangnya Sudah Dilaporkan
                    <a href="{{ route('reportdates.reportedlist',$report_date_id) }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>
                <div class="card-body table-responsive">
                    <p class="text-muted small">
                        Daftar berikut lintas semua periode penarikan laporan. Tombol pada kolom action
                        menyusulkan (menambahkan) data sempro/semhas mahasiswa yang sama &mdash; kalau tersedia
                        dan belum pernah dilaporkan ke periode manapun &mdash; ke periode yang sama dengan sidangnya.
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
