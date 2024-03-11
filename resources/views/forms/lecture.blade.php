@extends('layouts.setting-form')

@push('header')
    {{ $lecture->id ? 'Edit' : 'Tambah' }} Lecture
@endpush

@push('body')

<form id="formAction" action="{{ $lecture->id ? route('lectures.update',$lecture->id) : route('lectures.store') }}" method="post">
    @csrf
    @if ($lecture->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- jurusan --}}
        <div class="row mb-3">
            <label for="departement_id" class="col-md-4 col-form-label text-md-end">Jurusan</label>
            <div class="col-md-8">
                <select id="departement_id" class="form-control @error('departement') is-invalid @enderror" name="departement_id">
                    <option value="">-- Umum --</option>
                    @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected($lecture->departement_id==$departement->id)>{{ $departement->id.' - '.$departement->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- gelar_depan --}}
        <div class="row mb-3">
            <label for="gelar_depan" class="col-md-4 col-form-label text-md-end">gelar_depan</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->gelar_depan }}" name="gelar_depan" class="form-control" id="gelar_depan">
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">nama</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->nama }}" name="nama" class="form-control" id="nama">
            </div>
        </div>
        {{-- gelar_belakang --}}
        <div class="row mb-3">
            <label for="gelar_belakang" class="col-md-4 col-form-label text-md-end">gelar_belakang</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->gelar_belakang }}" name="gelar_belakang" class="form-control" id="gelar_belakang">
            </div>
        </div>
        {{-- nidn --}}
        <div class="row mb-3">
        <label for="nidn" class="col-md-4 col-form-label text-md-end">nidn</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->nidn }}" name="nidn" class="form-control" id="nidn">
            </div>
        </div>
        {{-- nip --}}
        <div class="row mb-3">
        <label for="nip" class="col-md-4 col-form-label text-md-end">nip</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->nip }}" name="nip" class="form-control" id="nip">
            </div>
        </div>
        {{-- jabatan_akademik --}}
        <div class="row mb-3">
            <label for="jabatan_akademik" class="col-md-4 col-form-label text-md-end">jabatan_akademik</label>
            <div class="col-md-8">
                <select id="jabatan_akademik" class="form-control @error('jabatan_akademik') is-invalid @enderror" name="jabatan_akademik">
                    <option value=""></option>
                    @foreach ($jabatan_akademiks as $jabatan_akademik)
                    <option value="{{ $jabatan_akademik }}" @selected($lecture->jabatan_akademik==$jabatan_akademik)>{{ $jabatan_akademik }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- golongan --}}
        <div class="row mb-3">
            <label for="golongan" class="col-md-4 col-form-label text-md-end">golongan</label>
            <div class="col-md-8">
                <select id="golongan" class="form-control @error('golongan') is-invalid @enderror" name="golongan">
                    <option value=""></option>
                    @foreach ($golongans as $golongan)
                    <option value="{{ $golongan }}" @selected($lecture->golongan==$golongan)>{{ $golongan }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- pendidikan --}}
        <div class="row mb-3">
            <label for="pendidikan" class="col-md-4 col-form-label text-md-end">pendidikan</label>
            <div class="col-md-8">
                <select id="pendidikan" class="form-control @error('pendidikan') is-invalid @enderror" name="pendidikan">
                    <option value=""></option>
                    @foreach ($pendidikans as $pendidikan)
                    <option value="{{ $pendidikan }}" @selected($lecture->pendidikan==$pendidikan)>{{ $pendidikan }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- Tempat Lahir --}}
        <div class="row mb-3">
            <label for="tempat_lahir" class="col-md-4 col-form-label text-md-end">Tempat Lahir</label>
            <div class="col-md-8">
                <input type="text" placeholder="tempat_lahir" value="{{ $lecture->tempat_lahir }}" name="tempat_lahir" class="form-control" id="tempat_lahir">
            </div>
        </div>
        {{-- Tanggal lahir --}}
        <div class="row mb-4">
            <label for="tanggal_lahir" class="col-md-4 col-form-label text-md-end">Tanggal lahir</label>
            <div class="col-md-8">
                <input type="date" placeholder="tanggal_lahir" value="{{ $lecture->tanggal_lahir ? $lecture->tanggal_lahir->format('Y-m-d') : '' }}" name="tanggal_lahir" class="form-control" id="tanggal_lahir">
            </div>
        </div>
        {{-- rekening --}}
        <div class="row mb-3">
        <label for="rekening" class="col-md-4 col-form-label text-md-end">rekening</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->rekening }}" name="rekening" class="form-control" id="rekening">
            </div>
        </div>
        {{-- npwp --}}
        <div class="row mb-3">
        <label for="npwp" class="col-md-4 col-form-label text-md-end">npwp</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->npwp }}" name="npwp" class="form-control" id="npwp">
            </div>
        </div>
        {{-- nik --}}
        <div class="row mb-3">
        <label for="nik" class="col-md-4 col-form-label text-md-end">nik</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->nik }}" name="nik" class="form-control" id="nik">
            </div>
        </div>
        {{-- email --}}
        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">email</label>
            <div class="col-md-8">
                <input type="email" value="{{ $lecture->email }}" name="email" class="form-control" id="email">
            </div>
        </div>
        {{-- phone --}}
        <div class="row mb-3">
            <label for="phone" class="col-md-4 col-form-label text-md-end">phone</label>
            <div class="col-md-8">
                <input type="text" value="{{ $lecture->phone }}" name="phone" class="form-control" id="phone">
                @if (!$lecture->phone==null)
                <a href="{{ 'http://wa.me/62' }}{{ $lecture->phone }}" target="_blank" class="text-primary">kirim wa</a>
                @endif
            </div>
        </div>
        {{-- alamat --}}
        <div class="row mb-3">
            <label for="alamat" class="col-md-4 col-form-label text-md-end">Alamat</label>
            <div class="col-md-8">
                <textarea name="alamat" rows="5" class="form-control" id="alamat">{{ $lecture->alamat }}</textarea>
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <label class="col-md-4 col-form-label text-md-end"></label>
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('lectures.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endpush
