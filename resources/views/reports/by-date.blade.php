@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    {{ __('Rekap Penyelenggaraan Ujian hari ').Carbon\Carbon::createFromFormat('Y-m-d',$date)->isoFormat('dddd, LL') }}
                    <a href="{{ route('reports.by.periode',substr($date,0,7)) }}" class="btn btn-sm btn-primary float-end">kembali</a>
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
