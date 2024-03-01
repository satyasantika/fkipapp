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
        {{-- name --}}
        <div class="row mb-3">
            <label for="name" class="col-md-4 col-form-label text-md-end">name</label>
            <div class="col-md-8">
                <input type="text" value="{{ $student->name }}" name="name" class="form-control" id="name">
            </div>
        </div>
        {{-- jurusan --}}
        <div class="row mb-3">
            <label for="departement_id" class="col-md-4 col-form-label text-md-end">Jurusan</label>
            <div class="col-md-8">
                <select id="departement_id" class="form-control @error('departement') is-invalid @enderror" name="departement_id">
                    <option value="">-- Umum --</option>
                    @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected($student->departement_id==$departement->id)>{{ $departement->id.' - '.$departement->name }}</option>
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
                <a href="{{ 'http://wa.me/62' }}{{ $student->phone }}" class="text-primary">kirim wa</a>
            </div>
        </div>
        {{-- pembimbing 1 --}}
        <div class="row mb-3">
            <label for="pembimbing1" class="col-md-4 col-form-label text-md-end">Pembimbing 1</label>
            <div class="col-md-8">
                <select id="pembimbing1" class="form-control @error('pembimbing1') is-invalid @enderror" name="pembimbing1">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->pembimbing1==$lecture->id)>{{ $lecture->name.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- pembimbing 2 --}}
        <div class="row mb-3">
            <label for="pembimbing2" class="col-md-4 col-form-label text-md-end">Pembimbing 2</label>
            <div class="col-md-8">
                <select id="pembimbing2" class="form-control @error('pembimbing2') is-invalid @enderror" name="pembimbing2">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->pembimbing2==$lecture->id)>{{ $lecture->name.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- penguji 1 --}}
        <div class="row mb-3">
            <label for="penguji1" class="col-md-4 col-form-label text-md-end">penguji 1</label>
            <div class="col-md-8">
                <select id="penguji1" class="form-control @error('penguji1') is-invalid @enderror" name="penguji1">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->penguji1==$lecture->id)>{{ $lecture->name.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- penguji 2 --}}
        <div class="row mb-3">
            <label for="penguji2" class="col-md-4 col-form-label text-md-end">penguji 2</label>
            <div class="col-md-8">
                <select id="penguji2" class="form-control @error('penguji2') is-invalid @enderror" name="penguji2">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->penguji2==$lecture->id)>{{ $lecture->name.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- penguji 3 --}}
        <div class="row mb-3">
            <label for="penguji3" class="col-md-4 col-form-label text-md-end">penguji 3</label>
            <div class="col-md-8">
                <select id="penguji3" class="form-control @error('penguji3') is-invalid @enderror" name="penguji3">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->penguji3==$lecture->id)>{{ $lecture->name.' - '.$lecture->departement_id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- ketua penguji --}}
        <div class="row mb-3">
            <label for="ketuapenguji" class="col-md-4 col-form-label text-md-end">ketua penguji</label>
            <div class="col-md-8">
                <select id="ketuapenguji" class="form-control @error('ketuapenguji') is-invalid @enderror" name="ketuapenguji">
                    <option value="">-- Tentukan --</option>
                    @foreach ($lectures as $lecture)
                    <option value="{{ $lecture->id }}" @selected($student->ketuapenguji==$lecture->id)>{{ $lecture->name.' - '.$lecture->departement_id }}</option>
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
            <label for="tanggal_sidang" class="col-md-4 col-form-label text-md-end">Tanggal sidang</label>
            <div class="col-md-6">
                <input type="date" placeholder="tanggal_sidang" value="{{ $student->tanggal_sidang ? $student->tanggal_sidang->format('Y-m-d') : "" }}" name="tanggal_sidang" class="form-control" id="tanggal_sidang">
            </div>
        </div>

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
