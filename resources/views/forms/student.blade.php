@extends('layouts.setting-form')

@push('header')
    {{ $student->id ? 'Edit' : 'Tambah' }} Student
@endpush

@push('body')

<form id="formAction" action="{{ $student->id ? route('students.update',$student->id) : route('students.store') }}" method="post">
    @csrf
    @if ($student->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- nim --}}
        <div class="row mb-3">
        <label for="nim" class="col-md-4 col-form-label text-md-end">nim</label>
            <div class="col-md-8">
                <input type="text" value="{{ $student->nim }}" name="nim" class="form-control" id="nim">
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">nama</label>
            <div class="col-md-8">
                <input type="text" value="{{ $student->nama }}" name="nama" class="form-control" id="nama">
            </div>
        </div>
        {{-- jurusan --}}
        <div class="row mb-3">
            <label for="departement_id" class="col-md-4 col-form-label text-md-end">Jurusan</label>
            <div class="col-md-8">
                <select id="departement_id" class="form-control @error('departement') is-invalid @enderror" name="departement_id" @disabled($student->departement_id==auth()->user()->departement_id)>
                    <option value="">-- Umum --</option>
                    @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected($student->departement_id==$departement->id)>{{ $departement->id.' - '.$departement->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- email --}}
        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">email</label>
            <div class="col-md-8">
                <input type="email" value="{{ $student->email }}" name="email" class="form-control" id="email">
            </div>
        </div>
        {{-- phone --}}
        <div class="row mb-3">
            <label for="phone" class="col-md-4 col-form-label text-md-end">phone</label>
            <div class="col-md-8">
                <input type="text" value="{{ $student->phone }}" name="phone" class="form-control" id="phone">
                @if (!$student->phone==null)
                <a href="{{ 'http://wa.me/62' }}{{ $student->phone }}" target="_blank" class="text-primary">kirim wa</a>
                @endif

            </div>
        </div>

    @role('jurusan')
        {{-- pembimbing 1 --}}
        <div class="row mb-3">
            <label for="pembimbing1_id" class="col-md-4 col-form-label text-md-end">Pembimbing 1</label>
            <div class="col-md-8">
                <select id="pembimbing1_id" class="form-control @error('pembimbing1_id') is-invalid @enderror" name="pembimbing1_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->pembimbing1_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- pembimbing 2 --}}
        <div class="row mb-3">
            <label for="pembimbing2_id" class="col-md-4 col-form-label text-md-end">Pembimbing 2</label>
            <div class="col-md-8">
                <select id="pembimbing2_id" class="form-control @error('pembimbing2_id') is-invalid @enderror" name="pembimbing2_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->pembimbing2_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- penguji 1 --}}
        <div class="row mb-3">
            <label for="penguji1_id" class="col-md-4 col-form-label text-md-end">penguji 1</label>
            <div class="col-md-8">
                <select id="penguji1_id" class="form-control @error('penguji1_id') is-invalid @enderror" name="penguji1_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->penguji1_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- penguji 2 --}}
        <div class="row mb-3">
            <label for="penguji2_id" class="col-md-4 col-form-label text-md-end">penguji 2</label>
            <div class="col-md-8">
                <select id="penguji2_id" class="form-control @error('penguji2_id') is-invalid @enderror" name="penguji2_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->penguji2_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- penguji 3 --}}
        <div class="row mb-3">
            <label for="penguji3_id" class="col-md-4 col-form-label text-md-end">penguji 3</label>
            <div class="col-md-8">
                <select id="penguji3_id" class="form-control @error('penguji3_id') is-invalid @enderror" name="penguji3_id">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->penguji3_id==$lecture->id)>{{ $lecture->nama.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
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
        {{-- Tanggal Proposal --}}
        <div class="row mb-3">
            <label for="tanggal_proposal" class="col-md-4 col-form-label text-md-end">Tanggal Proposal</label>
            <div class="col-md-6">
                <input type="date" placeholder="tanggal_proposal" value="{{ $student->tanggal_proposal ? $student->tanggal_proposal->format('Y-m-d') : "" }}" name="tanggal_proposal" class="form-control" id="tanggal_proposal">
            </div>
        </div>
        {{-- Tanggal seminar --}}
        <div class="row mb-3">
            <label for="tanggal_seminar" class="col-md-4 col-form-label text-md-end">Tanggal seminar</label>
            <div class="col-md-6">
                <input type="date" placeholder="tanggal_seminar" value="{{ $student->tanggal_seminar ? $student->tanggal_seminar->format('Y-m-d') : "" }}" name="tanggal_seminar" class="form-control" id="tanggal_seminar">
            </div>
        </div>
        {{-- Tanggal sidang --}}
        <div class="row mb-3">
            <label for="tanggal_skripsi" class="col-md-4 col-form-label text-md-end">Tanggal sidang</label>
            <div class="col-md-6">
                <input type="date" placeholder="tanggal_skripsi" value="{{ $student->tanggal_skripsi ? $student->tanggal_skripsi->format('Y-m-d') : "" }}" name="tanggal_skripsi" class="form-control" id="tanggal_skripsi">
            </div>
        </div>
    @endrole

        {{-- submit Button --}}
        <div class="row mb-0">
            <label class="col-md-4 col-form-label text-md-end"></label>
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endpush
