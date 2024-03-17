@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Rekap Penguji pada tanggal ').$date }}
                    <a href="{{ route('reports.by.periode',substr($date,0,7)) }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>

                <div class="card-body">
                    <div class="accordion" id="accordionExaminer">
                        @forelse ($examiner_ids as $examiner_id)
                        @php
                            $examregistrations = App\Models\ViewExamRegistration::where('tanggal_ujian',$date)
                                                ->where('pembimbing1_id',$examiner_id)
                                                ->orWhere('pembimbing2_id',$examiner_id)
                                                ->orWhere('penguji1_id',$examiner_id)
                                                ->orWhere('penguji2_id',$examiner_id)
                                                ->orWhere('penguji3_id',$examiner_id)
                                                ->get();
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $examiner_id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $examiner_id }}" aria-expanded="true" aria-controls="collapse{{ $examiner_id }}">
                                {{ \App\Models\Lecture::find($examiner_id)->nama }} &nbsp; <span class="badge bg-primary"> {{ $examregistrations->count() }}</span>
                            </button>
                            </h2>
                            <div id="collapse{{ $examiner_id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $examiner_id }}" data-bs-parent="#accordionExaminer">
                            <div class="accordion-body">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Ruangan</th>
                                            <th>Waktu</th>
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
                                        @foreach ($examregistrations as $examregistration)
                                        <tr>
                                            <td>{{ $examregistration->ruangan }}</td>
                                            <td>{{ $examregistration->waktu_mulai.' - '.$examregistration->waktu_akhir }}</td>
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
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                        @empty
                            tidak ada data
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
