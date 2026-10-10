@extends('layouts.portal')

@section('title', 'Mata pelajaran')
@section('breadcrumb', 'Mata pelajaran')

@section('content')
<div class="page-heading"><div><span class="eyebrow eyebrow-green">KURIKULUM</span><h1>Mata pelajaran</h1><p>Atur mata pelajaran dan kelas yang diampu.</p></div></div>
@if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
@if ($errors->any())<div class="notice notice-error">Periksa kembali data yang dimasukkan. {{ $errors->first() }}</div>@endif
<div class="management-grid subjects-grid">
    <section class="surface management-form">
        <div class="surface-heading"><div><span class="eyebrow eyebrow-green">KURIKULUM BARU</span><h2>Tambah mata pelajaran</h2></div></div>
        <form method="POST" action="{{ route('subjects.store') }}" class="field-grid">@csrf
            <label class="field field-wide">Nama mata pelajaran<input name="name" value="{{ old('name') }}" placeholder="Contoh: Matematika" required></label>
            <label class="field">Kode<input name="code" value="{{ old('code') }}" placeholder="MTK-7" required></label>
            <label class="field">Tingkat<select name="grade_level" required><option value="">Pilih kelas</option>@foreach ([7, 8, 9] as $grade)<option value="{{ $grade }}" @selected(old('grade_level') == $grade)>Kelas {{ $grade }}</option>@endforeach</select></label>
            @if (auth()->user()->isAdmin())
                <label class="field field-wide">Guru pengampu<select name="teacher_id"><option value="">Belum ditentukan</option>@foreach ($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></label>
            @endif
            <div class="field-wide"><button class="action-button" type="submit">Simpan mata pelajaran <span aria-hidden="true">&rarr;</span></button></div>
        </form>
    </section>
    <section class="surface records-surface">
        <div class="surface-heading"><div><span class="eyebrow eyebrow-green">DAFTAR KURIKULUM</span><h2>Mata pelajaran aktif</h2></div><span class="record-count">{{ $subjects->count() }} mapel</span></div>
        <div class="records-list">
            @forelse ($subjects as $subject)
                <div class="record-row subject-row"><span class="subject-code">{{ $subject->code }}</span><div class="record-main"><strong>{{ $subject->name }}</strong><small>Kelas {{ $subject->grade_level }}@if ($subject->teacher) · {{ $subject->teacher->name }}@endif</small></div><span class="role-tag">{{ $subject->exams_count }} ujian</span>
                    @if ($subject->exams_count === 0 && (auth()->user()->isAdmin() || $subject->teacher_id === auth()->id()))<form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mata pelajaran ini?')">@csrf @method('DELETE')<button class="icon-action" aria-label="Hapus {{ $subject->name }}" title="Hapus">×</button></form>@endif
                </div>
            @empty
                <div class="blank-state">Belum ada mata pelajaran.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection