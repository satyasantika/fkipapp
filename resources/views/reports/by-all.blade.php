@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Rekap Penyelenggaraan Ujian') }}</div>

                <div class="card-body">

                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th></th>
                                <th>Bulan laporan</th>
                                <th>Banyak Ujian</th>
                                <th>Penguji</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lists as $list)
                                <tr>
                                    <td>
                                        <a href="{{ route('reports.by.periode',$list->kode_laporan) }}" class="btn btn-sm btn-outline-primary">view</a>
                                    </td>
                                    <td>
                                        {{ Carbon\Carbon::createFromFormat('Y-m',$list->kode_laporan)->isoFormat('MMMM Y') }}
                                    </td>
                                    <td>
                                        @php
                                            $penguji = App\Models\ViewExamRegistration::where('kode_laporan',$list->kode_laporan)->where('departement_id',auth()->user()->departement_id);
                                        @endphp
                                        {{ $penguji->count() }}
                                    </td>
                                    <td>
                                        @php
                                            $pembimbing1 = $penguji->whereNotNull('pembimbing1_id')->pluck('pembimbing1_id');
                                            $pembimbing2 = $penguji->whereNotNull('pembimbing2_id')->pluck('pembimbing2_id');
                                            $penguji1 = $penguji->whereNotNull('penguji1_id')->pluck('penguji1_id');
                                            $penguji2 = $penguji->whereNotNull('penguji2_id')->pluck('penguji2_id');
                                            $penguji3 = $penguji->whereNotNull('penguji3_id')->pluck('penguji3_id');
                                            $collection = collect($pembimbing1)->concat($pembimbing2)->concat($penguji1)->concat($penguji2)->concat($penguji3);
                                        @endphp
                                        {{ $collection->unique()->values()->count() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">belum ada laporan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
