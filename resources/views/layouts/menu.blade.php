@role('admin')
<a class="dropdown-item" href="{{ route('users.index') }}">{{ __('User') }}</a>
<a class="dropdown-item" href="{{ route('students.index') }}">{{ __('Mahasiswa') }}</a>
<a class="dropdown-item" href="{{ route('lectures.index') }}">{{ __('Dosen') }}</a>
<a class="dropdown-item" href="{{ route('registrations.index') }}">{{ __('Reg Ujian') }}</a>
<a class="dropdown-item" href="{{ route('view-exam-examiners.index') }}">{{ __('Penguji') }}</a>
@endrole

@role('jurusan')
<a class="dropdown-item" href="{{ route('students.index') }}">{{ __('Mahasiswa') }}</a>
<a class="dropdown-item" href="{{ route('lectures.index') }}">{{ __('Dosen') }}</a>
<a class="dropdown-item" href="{{ route('registrations.index') }}">{{ __('Reg Ujian') }}</a>
<a class="dropdown-item" href="{{ route('departement-exam-examiners.index') }}">{{ __('Penguji') }}</a>
@endrole

@role('keuangan')
{{-- <a class="dropdown-item" href="{{ route('users.index') }}">{{ __('User') }}</a> --}}
@endrole

@role('dekanat')
{{-- <a class="dropdown-item" href="{{ route('users.index') }}">{{ __('User') }}</a> --}}
@endrole

