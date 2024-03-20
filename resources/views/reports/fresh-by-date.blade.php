@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="card">
                <div class="card-header">
                    {{ __('Rekap Ujian pada bulan ').Carbon\Carbon::createFromFormat('Y-m',$periode)->isoFormat('MMMM Y') }}
                    <a href="{{ route('paymentreports.index') }}" class="btn btn-sm btn-primary float-end">kembali</a>
                </div>

                <div class="card-body">
                    <h4>
                        TOTAL: {{ $total }} Ujian
                    </h4>
                    <hr>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-hover table-responsive table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th></th>
                                <th>tanggal ujian</th>
                                <th>peserta</th>
                                <th>jurusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dates as $date)
                            @php
                                $departement = App\Models\ViewExamRegistration::where('tanggal_ujian',$date)->pluck('departement_id');
                                $peserta = App\Models\ViewExamRegistration::where('tanggal_ujian',$date)->count();
                                $departement_id = collect($departement)->unique()->sort()->values()->all();
                                $tanggal = Carbon\Carbon::createFromFormat('Y-m-d',$date)->isoFormat('dddd, LL');
                            @endphp
                                <tr>
                                    <td>
                                        <form id="refresh-{{ $date }}" action="{{ route('reports.mass',$date) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-dark btn-sm" onclick="return confirm('data ujian tanggal {{ $tanggal }} akan disegarkan');">
                                                {{ __('fresh') }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>{{ $tanggal }}</td>
                                    <td class="text-center">{{ $peserta }}</td>
                                    <td>@foreach ($departement_id as $id)
                                        {{ App\Models\Departement::find($id)->mapel }}
                                        <span class="badge bg-dark">{{ App\Models\ViewExamRegistration::where('departement_id',$id)->where('tanggal_ujian',$date)->count() }}</span>&nbsp;|
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
