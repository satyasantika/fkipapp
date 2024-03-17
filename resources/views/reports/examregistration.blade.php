@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    {{ __('Registrasi Ujian') }}
                    <a href="{{ route('students.index') }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>

                <div class="card-body">
                    <a href="{{ route('registrations.student',$student_id) }}" class="btn btn-sm btn-success">+ jadwal ujian</a>
                    <a href="{{ route('registrations.index',) }}" class="btn btn-sm btn-primary float-end">semua jadwal ujian</a>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>action</th>
                                <th>Tanggal ujian</th>
                                <th>Ujian</th>
                                <th>Mahasiswa</th>
                                <th>NPM</th>
                                <th>Pembimbing1</th>
                                <th>Pembimbing2</th>
                                <th>Penguji1</th>
                                <th>Penguji2</th>
                                <th>Penguji3</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($examregistrations as $examregistration)
                                <tr class="table-{{ $examregistration->dilaporkan ? 'success' : '' }}">
                                    <td><a href="{{ route('registrations.edit',$examregistration->id) }}" class="btn btn-sm btn-primary">view</a></td>
                                    <td>{{ $examregistration->tanggal_ujian }}</td>
                                    <td>{{ $examregistration->ujian }}</td>
                                    <td>{{ $examregistration->mahasiswa }}</td>
                                    <td>{{ $examregistration->nim }}</td>
                                    <td>{{ $examregistration->pembimbing1_nama }}</td>
                                    <td>{{ $examregistration->pembimbing2_nama }}</td>
                                    <td>{{ $examregistration->penguji1_nama }}</td>
                                    <td>{{ $examregistration->penguji2_nama }}</td>
                                    <td>{{ $examregistration->penguji3_nama }}</td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">belum ada data ujian</td>
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
