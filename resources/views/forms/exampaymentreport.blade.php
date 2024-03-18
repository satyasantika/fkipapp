@extends('layouts.setting-form')

@push('header')
    Edit Data laporan untuk dosen {{ $paymentreport->dosen }}
        <form id="delete-form" action="{{ route('paymentreports.destroy',$paymentreport->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus data penguji {{ $paymentreport->dosen }}?');">
                {{ __('del') }}
            </button>
        </form>
@endpush

@push('body')

<form id="formAction" action="{{ route('paymentreports.update',$paymentreport->id) }}" method="post">
    @csrf
    @method('PUT')
    <div class="card-body">
        {{-- nama --}}
        <div class="row mb-3">
            <label for="nama" class="col-md-4 col-form-label text-md-end">nama</label>
            <div class="col-md-8">
                <input type="text" value="{{ $paymentreport->dosen }}" name="nama" class="form-control" id="nama" @disabled(true)>
            </div>
        </div>
        {{-- status pns --}}
        <div class="row mb-3">
            <label for="pns" class="col-md-4 col-form-label text-md-end">Status</label>
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-text">
                        <input id="pns" name="pns" class="form-check-input mt-0" type="checkbox" @checked($paymentreport->pns)>
                    </div>
                    <div class="input-group-text">
                        PNS
                    </div>
                </div>
            </div>
        </div>
        {{-- golongan --}}
        <div class="row mb-3">
            <label for="golongan" class="col-md-4 col-form-label text-md-end">golongan</label>
            <div class="col-md-8">
                <select id="golongan" class="form-control @error('golongan') is-invalid @enderror" name="golongan">
                    <option value=""></option>
                    @foreach ($golongans as $golongan)
                    <option value="{{ $golongan }}" @selected($paymentreport->golongan==$golongan)>{{ $golongan }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- jabatan_akademik --}}
        <div class="row mb-3">
            <label for="jabatan_akademik" class="col-md-4 col-form-label text-md-end">Jabatan</label>
            <div class="col-md-8">
                <select id="jabatan_akademik" class="form-control @error('jabatan_akademik') is-invalid @enderror" name="jabatan_akademik">
                    <option value=""></option>
                    @foreach ($jabatan_akademiks as $jabatan_akademik)
                    <option value="{{ $jabatan_akademik }}" @selected($paymentreport->jabatan_akademik==$jabatan_akademik)>{{ $jabatan_akademik }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- pendidikan --}}
        <div class="row mb-3">
            <label for="pendidikan" class="col-md-4 col-form-label text-md-end">Pendidikan</label>
            <div class="col-md-8">
                <select id="pendidikan" class="form-control @error('pendidikan') is-invalid @enderror" name="pendidikan">
                    <option value=""></option>
                    @foreach ($pendidikans as $pendidikan)
                    <option value="{{ $pendidikan }}" @selected($paymentreport->pendidikan==$pendidikan)>{{ $pendidikan }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- npwp --}}
        <div class="row mb-3">
        <label for="npwp" class="col-md-4 col-form-label text-md-end">npwp</label>
            <div class="col-md-8">
                <input type="text" value="{{ $paymentreport->npwp }}" name="npwp" class="form-control" id="npwp">
            </div>
        </div>
        {{-- rekening --}}
        <div class="row mb-3">
        <label for="rekening" class="col-md-4 col-form-label text-md-end">rekening</label>
            <div class="col-md-8">
                <input type="text" value="{{ $paymentreport->rekening }}" name="rekening" class="form-control" id="rekening">
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('reports.date',['pns'=>$paymentreport->status,'kode_laporan'=>$paymentreport->kode_laporan]) }}" class="btn btn-outline-secondary btn-sm">Close</a>
            </div>
        </div>
    </div>
</form>
<hr>

@endpush
