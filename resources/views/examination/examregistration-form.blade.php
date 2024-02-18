@extends('layouts.setting-form')

@push('header')
    {{ $examregistration->id ? 'Edit' : 'Tambah' }} Tanggal Ujian di Jurusan {{ auth()->user()->departement->name }}
    @if ($examregistration->id)
        <form id="delete-form" action="{{ route('examregistrations.destroy',$examregistration->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $examregistration->name }}?');">
                {{ __('del') }}
            </button>
        </form>
    @endif
@endpush

@push('body')

<form id="formAction" action="{{ $examregistration->id ? route('examregistrations.update',$examregistration->id) : route('examregistrations.store') }}" method="post">
    @csrf
    @if ($examregistration->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- Tanggal Ujian --}}
        <div class="row mb-3">
            <label for="tanggal_ujian" class="col-md-4 col-form-label text-md-end">Set</label>
            <div class="col-md-8">
                <input type="date" placeholder="tanggal_ujian" value="{{ $examregistration->tanggal_ujian ? $examregistration->tanggal_ujian->format('Y-m-d') : date('Y-m-d') }}" name="tanggal_ujian" class="form-control" id="tanggal_ujian">
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('examregistrations.index') }}" class="btn btn-outline-secondary btn-sm">Close</a>
            </div>
        </div>
    </div>
</form>

@endpush
