@role('admin')
<a class="dropdown-item" href="{{ route('users.index') }}">{{ __('User') }}</a>
<a class="dropdown-item" href="{{ route('students.index') }}">{{ __('Mahasiswa') }}</a>
<a class="dropdown-item" href="{{ route('lectures.index') }}">{{ __('Dosen') }}</a>
@endrole

@role('jurusan')
<a class="dropdown-item" href="{{ route('students.index') }}">{{ __('Mahasiswa') }}</a>
<a class="dropdown-item" href="{{ route('registrations.index') }}">{{ __('Reg Ujian') }}</a>
<a class="dropdown-item" href="{{ route('reports.by.departement') }}">{{ __('Rekap Ujian') }}</a>
@endrole

@role('keuangan')
<a class="dropdown-item" href="{{ route('lectures.index') }}">{{ __('Dosen') }}</a>
<a class="dropdown-item" href="{{ route('paymentreports.index') }}">{{ __('Laporan') }}</a>
@endrole

@role('dekanat')
{{-- <a class="dropdown-item" href="{{ route('users.index') }}">{{ __('User') }}</a> --}}
@endrole

