@extends('layouts.setting-form')

@push('header')
    {{ $reportdate->id ? 'Edit' : 'Tambah' }} tanggal penarikan laporan
    @if (!$ada_laporan)
        <form id="delete-form" action="{{ route('reportdates.destroy',$reportdate->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus data penarikan tanggal {{ Carbon\Carbon::parse($reportdate->tanggal)->isoFormat('LL') }}?');">
                {{ __('del') }}
            </button>
        </form>
    @endif
@endpush

@push('body')

<form id="formAction" action="{{ $reportdate->id ? route('reportdates.update',$reportdate->id) : route('reportdates.store') }}" method="post">
    @csrf
    @if ($reportdate->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- Tanggal Penarikan --}}
        <div class="row mb-3">
            <label for="tanggal" class="col-md-4 col-form-label text-md-end">Tanggal Penarikan</label>
            <div class="col-md-8">
                <input type="date" placeholder="tanggal" value="{{ $reportdate->tanggal }}" name="tanggal" class="form-control" id="tanggal">
            </div>
        </div>
        {{-- deskripsi --}}
        <div class="row mb-3">
            <label for="deskripsi" class="col-md-4 col-form-label text-md-end">Deskripsi Laporan</label>
            <div class="col-md-8">
                <textarea name="deskripsi" rows="5" class="form-control" id="deskripsi" placeholder="">{{ $reportdate->deskripsi }}</textarea>
            </div>
        </div>
        @if ($reportdate->id)
        {{-- dibayar --}}
        <div class="row mb-3">
            <label for="dibayar" class="col-md-4 col-form-label text-md-end">dibayar</label>
            <div class="col-md-8">
                <input type="number" placeholder="dibayar" value="{{ $reportdate->dibayar }}" name="dibayar" class="form-control" id="dibayar" disabled>
            </div>
        </div>
        @endif
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('reportdates.index') }}" class="btn btn-outline-secondary btn-sm">Close</a>
            </div>
        </div>
    </div>
</form>
@endpush
