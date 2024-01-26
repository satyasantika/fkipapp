@extends('layouts.setting-form')

@push('header')
    {{ $examdate->id ? 'Edit' : 'Tambah' }} Tanggal Ujian di Jurusan {{ auth()->user()->departement->name }}
    @if ($examdate->id)
        <form id="delete-form" action="{{ route('examdates.destroy',$examdate->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $examdate->name }}?');">
                {{ __('del') }}
            </button>
        </form>
    @endif
@endpush

@push('body')

<form id="formAction" action="{{ $examdate->id ? route('examdates.update',$examdate->id) : route('examdates.store') }}" method="post">
    @csrf
    @if ($examdate->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- Tanggal Ujian --}}
        <div class="row mb-3">
            <label for="tanggal_ujian" class="col-md-4 col-form-label text-md-end">Set</label>
            <div class="col-md-8">
                <input type="date" placeholder="tanggal_ujian" value="{{ $examdate->tanggal_ujian ? $examdate->tanggal_ujian->format('Y-m-d') : date('Y-m-d') }}" name="tanggal_ujian" class="form-control" id="tanggal_ujian">
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('examdates.index') }}" class="btn btn-outline-secondary btn-sm">Close</a>
            </div>
        </div>
    </div>
</form>

@endpush
