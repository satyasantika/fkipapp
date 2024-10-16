@extends('layouts.setting-form')

@push('header')
    {{ $examregistration->id ? 'Edit' : 'Tambah' }} Ujian untuk {{ $student->nama }}
    @if ($examregistration->id &&  !$examregistration->dilaporkan)
        <form id="delete-form" action="{{ route('registrations.destroy',$examregistration->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus data {{ $examregistration->exam_type->nama_ujian }} {{ $student->nama }}?');">
                {{ __('del') }}
            </button>
        </form>
    @endif
@endpush

@push('body')

<form id="formAction" action="{{ $examregistration->id ? route('registrations.update',$examregistration->id) : route('registrations.store') }}" method="post">
    @csrf
    @if ($examregistration->id)
        @method('PUT')
    @endif
    <div class="card-body">
        @if ($examregistration->dilaporkan)
        <div class="alert alert-info">
            <h3>Ujian ini sudah dilaporkan</h3>
            Anda tidak dapat mengedit tanggal ujian dan susunan para penguji
        </div>
        @endif

        <input type="hidden" name="departement_id" value="{{ $student->departement_id }}">
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        {{-- jenis ujian --}}
        <div class="row mb-3">
            <label for="exam_type_id" class="col-md-4 col-form-label text-md-end">Jenis Ujian</label>
            <div class="col-md-8">
                <select id="exam_type_id" class="form-control @error('exam_type_id') is-invalid @enderror" name="exam_type_id" @disabled($examregistration->dilaporkan)>
                    <option value="">-- Tentukan --</option>
                    @foreach ($exam_types as $exam_type)
                    <option value="{{ $exam_type->id }}" @selected($examregistration->exam_type_id==$exam_type->id)>{{ $exam_type->nama_ujian }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- ujian ke- --}}
        <div class="row mb-3">
            <label for="ujian_ke" class="col-md-4 col-form-label text-md-end">Ujian Ke-</label>
            <div class="col-md-2">
                <select id="ujian_ke" class="form-control @error('ujian_ke') is-invalid @enderror" name="ujian_ke" required >
                    <option value="">pilih ...</option>
                    @foreach ([1,2,3] as $ujian_ke)
                    <option value="{{ $ujian_ke }}" @selected($examregistration->ujian_ke == $ujian_ke)>{{ $ujian_ke }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- Tanggal Ujian --}}
        <div class="row mb-3">
            <label for="tanggal_ujian" class="col-md-4 col-form-label text-md-end">Tanggal Ujian</label>
            <div class="col-md-8">
                <input type="date" placeholder="tanggal_ujian" value="{{ $examregistration->tanggal_ujian ? $examregistration->tanggal_ujian->format('Y-m-d') : '' }}" name="tanggal_ujian" class="form-control" id="tanggal_ujian" @disabled($examregistration->dilaporkan)>
            </div>
        </div>
        {{-- Exam Time Start --}}
        <div class="row mb-3">
            <label for="waktu_mulai" class="col-md-4 col-form-label text-md-end">Mulai Ujian</label>
            <div class="col-md-7">
                <input type="time" placeholder="waktu_mulai" value="{{ $examregistration->waktu_mulai }}" name="waktu_mulai" class="form-control" id="waktu_mulai">
            </div>
        </div>
        {{-- Exam Time Finish --}}
        <div class="row mb-3">
            <label for="waktu_akhir" class="col-md-4 col-form-label text-md-end">Akhir Ujian</label>
            <div class="col-md-7">
                <input type="time" placeholder="waktu_akhir" value="{{ $examregistration->waktu_akhir }}" name="waktu_akhir" class="form-control" id="waktu_akhir">
            </div>
        </div>
        {{-- room --}}
        <div class="row mb-3">
            <label for="ruangan" class="col-md-4 col-form-label text-md-end">Tempat Ujian</label>
            <div class="col-md-7">
                <select id="ruangan" class="form-control @error('ruangan') is-invalid @enderror" name="ruangan">
                    <option value="">-- Pilih Ruang Ujian --</option>
                    @foreach ([1,2,3,4] as $tempat)
                    <option value="{{ $tempat }}" @selected($examregistration->ruangan == $tempat)>Ruang Sidang {{ $tempat }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- judul penelitian --}}
        <div class="row mb-3">
            <label for="judul_penelitian" class="col-md-4 col-form-label text-md-end">Judul Penelitian</label>
            <div class="col-md-7">
                <textarea name="judul_penelitian" rows="5" class="form-control" id="judul_penelitian" placeholder="">{{ $examregistration->judul_penelitian }}</textarea>
            </div>
        </div>
        {{-- ipk --}}
        <div class="row mb-3">
            <label for="ipk" class="col-md-4 col-form-label text-md-end">IPK</label>
            <div class="col-md-2">
                <input
                    type="number"
                    value="{{ $examregistration->ipk }}"
                    name="ipk"
                    class="form-control"
                    id="ipk"
                    min="2.00"
                    max="4.00"
                    step="0.01">
            </div>
        </div>
    @if ($examregistration->id)
        <div class="row mb-3">
            <div class="col">
                <span class="text-danger">Pastikan Pembimbing dan Penguji diceklis jika akan dibayar</span>
            </div>
        </div>
        {{-- pembimbing 1 --}}
        <div class="row mb-3">
            <label for="pembimbing1_id" class="col-md-4 col-form-label text-md-end">Pembimbing 1</label>
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-text">
                        <input id="pembimbing1_dibayar" name="pembimbing1_dibayar" class="form-check-input mt-0" type="checkbox" @checked($examregistration->pembimbing1_dibayar) @disabled($examregistration->dilaporkan)>
                    </div>
                    <select id="pembimbing1_id" class="form-control @error('pembimbing1_id') is-invalid @enderror" name="pembimbing1_id" @disabled($examregistration->dilaporkan)>
                        <option value="">-- Tentukan --</option>
                        @foreach ($lectures as $lecture)
                        <option value="{{ $lecture->id }}" @selected($examregistration->pembimbing1_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- pembimbing 2 --}}
        <div class="row mb-3">
            <label for="pembimbing2_id" class="col-md-4 col-form-label text-md-end">Pembimbing 2</label>
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-text">
                        <input id="pembimbing2_dibayar" name="pembimbing2_dibayar" class="form-check-input mt-0" type="checkbox" @checked($examregistration->pembimbing2_dibayar) @disabled($examregistration->dilaporkan)>
                    </div>
                    <select id="pembimbing2_id" class="form-control @error('pembimbing2_id') is-invalid @enderror" name="pembimbing2_id" @disabled($examregistration->dilaporkan)>
                        <option value="">-- Tentukan --</option>
                        @foreach ($lectures as $lecture)
                        <option value="{{ $lecture->id }}" @selected($examregistration->pembimbing2_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- penguji 1 --}}
        <div class="row mb-3">
            <label for="penguji1_id" class="col-md-4 col-form-label text-md-end">penguji 1</label>
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-text">
                        <input id="penguji1_dibayar" name="penguji1_dibayar" class="form-check-input mt-0" type="checkbox" @checked($examregistration->penguji1_dibayar) @disabled($examregistration->dilaporkan)>
                    </div>
                    <select id="penguji1_id" class="form-control @error('penguji1_id') is-invalid @enderror" name="penguji1_id" @disabled($examregistration->dilaporkan)>
                        <option value="">-- Tentukan --</option>
                        @foreach ($lectures as $lecture)
                        <option value="{{ $lecture->id }}" @selected($examregistration->penguji1_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- penguji 2 --}}
        <div class="row mb-3">
            <label for="penguji2_id" class="col-md-4 col-form-label text-md-end">penguji 2</label>
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-text">
                        <input id="penguji2_dibayar" name="penguji2_dibayar" class="form-check-input mt-0" type="checkbox" @checked($examregistration->penguji2_dibayar) @disabled($examregistration->dilaporkan)>
                    </div>
                    <select id="penguji2_id" class="form-control @error('penguji2_id') is-invalid @enderror" name="penguji2_id" @disabled($examregistration->dilaporkan)>
                        <option value="">-- Tentukan --</option>
                        @foreach ($lectures as $lecture)
                        <option value="{{ $lecture->id }}" @selected($examregistration->penguji2_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- penguji 3 --}}
        <div class="row mb-3">
            <label for="penguji3_id" class="col-md-4 col-form-label text-md-end">penguji 3</label>
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-text">
                        <input id="penguji3_dibayar" name="penguji3_dibayar" class="form-check-input mt-0" type="checkbox" @checked($examregistration->penguji3_dibayar) @disabled($examregistration->dilaporkan)>
                    </div>
                    <select id="penguji3_id" class="form-control @error('penguji3_id') is-invalid @enderror" name="penguji3_id" @disabled($examregistration->dilaporkan)>
                        <option value="">-- Tentukan --</option>
                        @foreach ($lectures as $lecture)
                        <option value="{{ $lecture->id }}" @selected($examregistration->penguji3_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- ketua penguji --}}
        <div class="row mb-3">
            <label for="ketuapenguji_id" class="col-md-4 col-form-label text-md-end bg-warning">ketua penguji</label>
            <div class="col-md-8">
                <select id="ketuapenguji_id" class="form-control @error('ketuapenguji_id') is-invalid @enderror" name="ketuapenguji_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->ketuapenguji_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif


        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('registrations.show.student',['student_id'=>$student->id]) }}" class="btn btn-outline-secondary btn-sm">Close</a>
            </div>
        </div>
    </div>
</form>
<hr>
@hasrole('keuangan')
    @if ($examregistration->id)
        @if (!$examregistration->dilaporkan)
        <form id="report-form" action="{{ route('paymentreports.store') }}" method="post">
            @csrf
            <input type="hidden" name="examregistration_id" value="{{ $examregistration->id }}">
            <button type="submit" class="btn btn-success btn-sm float-end" onclick="return confirm('data {{ $examregistration->exam_type->nama_ujian }} {{ $student->nama }} akan dilaporkan ke atasan');" @disabled($examregistration->dilaporkan)>
                {{ __('laporkan ujian') }}
            </button>
        </form>
        @endif
        @if ($examregistration->dilaporkan)
        <form id="retract-form" action="{{ route('registrations.update',$examregistration->id) }}" method="post">
            @csrf
            @method('PUT')
            <input type="hidden" name="dilaporkan" value="0">
            <input type="hidden" name="pembimbing1_dibayar" value="{{ $examregistration->pembimbing1_dibayar }}">
            <input type="hidden" name="pembimbing2_dibayar" value="{{ $examregistration->pembimbing2_dibayar }}">
            <input type="hidden" name="penguji1_dibayar" value="{{ $examregistration->penguji1_dibayar }}">
            <input type="hidden" name="penguji2_dibayar" value="{{ $examregistration->penguji2_dibayar }}">
            <input type="hidden" name="penguji3_dibayar" value="{{ $examregistration->penguji3_dibayar }}">
            <button type="submit" class="btn btn-danger btn-sm float-end" onclick="return confirm('batalkan laporan?');">
                {{ __('cabut laporan') }}
            </button>
        </form>
        @endif
    @endif
@endhasrole
@endpush
