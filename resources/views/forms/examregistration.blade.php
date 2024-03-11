@extends('layouts.setting-form')

@push('header')
    {{ $registration->id ? 'Edit' : 'Tambah' }} Ujian untuk {{ $student->nama }}
    @if ($registration->id)
        <form id="delete-form" action="{{ route('registrations.destroy',$registration->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus data ujian {{ $student->nama }}?');">
                {{ __('del') }}
            </button>
        </form>
    @endif
@endpush

@push('body')

<form id="formAction" action="{{ $registration->id ? route('registrations.update',$registration->id) : route('registrations.store') }}" method="post">
    @csrf
    @if ($registration->id)
        @method('PUT')
    @endif
    <div class="card-body">
        <input type="hidden" name="departement_id" value="{{ $student->departement_id }}">
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        {{-- jenis ujian --}}
        <div class="row mb-3">
            <label for="exam_type_id" class="col-md-4 col-form-label text-md-end">Jenis Ujian</label>
            <div class="col-md-8">
                <select id="exam_type_id" class="form-control @error('exam_type_id') is-invalid @enderror" name="exam_type_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($exam_types as $exam_type)
                    <option value="{{ $exam_type->id }}" @selected($registration->exam_type_id==$exam_type->id)>{{ $exam_type->nama_ujian }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- ujian ke- --}}
        <div class="row mb-3">
            <label for="ujianke" class="col-md-4 col-form-label text-md-end">Ujian Ke-</label>
            <div class="col-md-2">
                <select id="ujianke" class="form-control @error('ujianke') is-invalid @enderror" name="ujianke" required >
                    <option value="">pilih ...</option>
                    @foreach ([1,2,3] as $ujianke)
                    <option value="{{ $ujianke }}" @selected($registration->ujian_ke == $ujianke)>{{ $ujianke }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- Tanggal Ujian --}}
        <div class="row mb-3">
            <label for="tanggal_ujian" class="col-md-4 col-form-label text-md-end">Set</label>
            <div class="col-md-8">
                <input type="date" placeholder="tanggal_ujian" value="{{ $registration->tanggal_ujian ? $registration->tanggal_ujian->format('Y-m-d') : '' }}" name="tanggal_ujian" class="form-control" id="tanggal_ujian">
            </div>
        </div>
        {{-- Exam Time Start --}}
        <div class="row mb-3">
            <label for="waktu_mulai" class="col-md-4 col-form-label text-md-end">Mulai Ujian</label>
            <div class="col-md-7">
                <input type="time" placeholder="waktu_mulai" value="{{ $registration->waktu_mulai }}" name="waktu_mulai" class="form-control" id="waktu_mulai">
            </div>
        </div>
        {{-- Exam Time Finish --}}
        <div class="row mb-3">
            <label for="waktu_akhir" class="col-md-4 col-form-label text-md-end">Akhir Ujian</label>
            <div class="col-md-7">
                <input type="time" placeholder="waktu_akhir" value="{{ $registration->waktu_akhir }}" name="waktu_akhir" class="form-control" id="waktu_akhir">
            </div>
        </div>
        {{-- room --}}
        <div class="row mb-3">
            <label for="tempat" class="col-md-4 col-form-label text-md-end">Tempat Ujian</label>
            <div class="col-md-7">
                <select id="tempat" class="form-control @error('tempat') is-invalid @enderror" name="tempat">
                    <option value="">-- Pilih Ruang Ujian --</option>
                    @foreach ([1,2,3,4] as $tempat)
                    <option value="{{ $tempat }}" @selected($registration->ruangan == $tempat)>Ruang Sidang {{ $tempat }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- judul penelitian --}}
        <div class="row mb-3">
            <label for="judul" class="col-md-4 col-form-label text-md-end">Judul Penelitian</label>
            <div class="col-md-7">
                <textarea name="judul" rows="5" class="form-control" id="judul" placeholder="">{{ $registration->judul }}</textarea>
            </div>
        </div>
        {{-- ipk --}}
        <div class="row mb-3">
            <label for="ipk" class="col-md-4 col-form-label text-md-end">IPK</label>
            <div class="col-md-2">
                <input
                    type="number"
                    value="{{ $registration->ipk }}"
                    name="ipk"
                    class="form-control"
                    id="ipk"
                    min="2.00"
                    max="4.00"
                    step="0.01">
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('registrations.index') }}" class="btn btn-outline-secondary btn-sm">Close</a>
            </div>
        </div>
    </div>
</form>

@endpush
