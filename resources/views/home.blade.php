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
                    <p>silakan pilih menu berikut</p><br>
                @role('admin')
                <a class="btn btn-primary" href="{{ route('users.index') }}">{{ __('User') }}</a>
                <a class="btn btn-primary" href="{{ route('students.index') }}">{{ __('Mahasiswa') }}</a>
                <a class="btn btn-primary" href="{{ route('lectures.index') }}">{{ __('Dosen') }}</a>
                {{-- <a class="btn btn-primary" href="{{ route('registrations.index') }}">{{ __('Reg Ujian') }}</a> --}}
                @endrole

                @role('jurusan')
                <a class="btn btn-primary" href="{{ route('students.index') }}">{{ __('Mahasiswa') }}</a>
                <a class="btn btn-primary" href="{{ route('lectures.index') }}">{{ __('Dosen') }}</a>
                <a class="btn btn-primary" href="{{ route('registrations.index') }}">{{ __('Reg Ujian') }}</a>
                @endrole

                @role('keuangan')
                {{-- <a class="btn btn-primary" href="{{ route('users.index') }}">{{ __('User') }}</a> --}}
                @endrole

                @role('dekanat')
                {{-- <a class="btn btn-primary" href="{{ route('users.index') }}">{{ __('User') }}</a> --}}
                @endrole
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
