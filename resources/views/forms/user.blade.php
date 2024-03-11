@extends('layouts.setting-form')

@push('header')
    {{ $user->id ? 'Edit' : 'Tambah' }} User
    @if ($user->id)
        <form id="delete-form" action="{{ route('users.destroy',$user->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $user->name }}?');">
                {{ __('del') }}
            </button>
        </form>
    @endif
@endpush

@push('body')

<form id="formAction" action="{{ $user->id ? route('users.update',$user->id) : route('users.store') }}" method="post">
    @csrf
    @if ($user->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- username --}}
        <div class="row mb-3">
        <label for="username" class="col-md-4 col-form-label text-md-end">username</label>
            <div class="col-md-8">
                <input type="text" value="{{ $user->username }}" name="username" class="form-control" id="username">
            </div>
        </div>
        {{-- name --}}
        <div class="row mb-3">
            <label for="name" class="col-md-4 col-form-label text-md-end">name</label>
            <div class="col-md-8">
                <input type="text" value="{{ $user->name }}" name="name" class="form-control" id="name">
            </div>
        </div>
        {{-- jurusan --}}
        <div class="row mb-3">
            <label for="departement_id" class="col-md-4 col-form-label text-md-end">Jurusan</label>
            <div class="col-md-8">
                <select id="departement_id" class="form-control @error('departement') is-invalid @enderror" name="departement_id">
                    <option value="">-- Umum --</option>
                    @foreach ($departements as $departement)
                    <option value="{{ $departement->id }}" @selected($user->departement_id==$departement->id)>{{ $departement->id.' - '.$departement->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- email --}}
        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">email</label>
            <div class="col-md-8">
                <input type="email" value="{{ $user->email }}" name="email" class="form-control" id="email">
            </div>
        </div>
        @if (!$user->id)
            {{-- password --}}
            <div class="row mb-3">
                <label for="password" class="col-md-4 col-form-label text-md-end">password</label>
                <div class="col-md-8">
                    <input type="password" value="{{ $user->password }}" name="password" class="form-control" id="password">
                </div>
            </div>
        @endif

        {{-- submit Button --}}
        <div class="row mb-0">
            <label class="col-md-4 col-form-label text-md-end"></label>
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endpush
