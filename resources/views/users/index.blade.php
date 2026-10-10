@extends('layouts.portal')

@section('title', 'Pengguna')
@section('breadcrumb', 'Pengguna')

@section('content')
<div class="page-heading"><div><span class="eyebrow eyebrow-green">ADMINISTRASI SEKOLAH</span><h1>Pengguna</h1><p>Kelola akun siswa, guru, dan administrator.</p></div></div>
@if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
@if ($errors->any())<div class="notice notice-error">Periksa kembali data yang dimasukkan. {{ $errors->first() }}</div>@endif
<div class="management-grid">
    <section class="surface management-form">
        <div class="surface-heading"><div><span class="eyebrow eyebrow-green">AKUN BARU</span><h2>Tambah pengguna</h2></div></div>
        <form method="POST" action="{{ route('users.store') }}" class="field-grid">@csrf
            <label class="field field-wide">Nama lengkap<input name="name" value="{{ old('name') }}" required maxlength="120"></label>
            <label class="field field-wide">Username<input name="username" value="{{ old('username') }}" required maxlength="50" autocomplete="username"></label>
            <label class="field field-wide">Email<input name="email" type="email" value="{{ old('email') }}" required></label>
            <label class="field">Peran<select name="role" required><option value="student">Siswa</option><option value="teacher" @selected(old('role') === 'teacher')>Guru</option><option value="admin" @selected(old('role') === 'admin')>Administrator</option></select></label>
            <label class="field">Kelas<input name="class_name" value="{{ old('class_name') }}" placeholder="Contoh: 7A"></label>
            <label class="field">Tingkat siswa<select name="grade_level"><option value="">Tidak berlaku</option>@foreach ([7, 8, 9] as $grade)<option value="{{ $grade }}" @selected(old('grade_level') == $grade)>Kelas {{ $grade }}</option>@endforeach</select></label>
            <label class="field">NIS/NISN<input name="student_number" value="{{ old('student_number') }}"></label>
            <label class="field">Kata sandi<input name="password" type="password" required minlength="10" autocomplete="new-password"></label>
            <label class="field field-wide">Ulangi kata sandi<input name="password_confirmation" type="password" required minlength="10" autocomplete="new-password"></label>
            <div class="field-wide"><button class="action-button" type="submit">Buat akun <span aria-hidden="true">&rarr;</span></button></div>
        </form>
    </section>
    <section class="surface records-surface">
        <div class="surface-heading"><div><span class="eyebrow eyebrow-green">DIREKTORI</span><h2>Semua akun</h2></div><span class="record-count">{{ $users->total() }} akun</span></div>
        <div class="records-list">
            @forelse ($users as $listedUser)
                <div class="record-row"><span class="record-avatar">{{ mb_strtoupper(mb_substr($listedUser->name, 0, 1)) }}</span><div class="record-main"><strong>{{ $listedUser->name }}</strong><small>{{ $listedUser->username }} · {{ $listedUser->email }}</small></div><span class="role-tag role-{{ $listedUser->role }}">{{ ['admin' => 'Admin', 'teacher' => 'Guru', 'student' => 'Siswa'][$listedUser->role] ?? $listedUser->role }}</span></div>
            @empty
                <div class="blank-state">Belum ada akun.</div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $users->links() }}</div>
    </section>
</div>
@endsection