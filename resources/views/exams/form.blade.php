@extends('layouts.portal')

@section('title', $exam->exists ? 'Ubah ujian' : 'Buat ujian')
@section('breadcrumb')<a href="{{ route('exams.index') }}">Ujian</a><span>/</span>{{ $exam->exists ? 'Ubah' : 'Baru' }}@endsection

@section('content')
<div class="page-heading"><div><span class="eyebrow eyebrow-green">PENILAIAN AKADEMIK</span><h1>{{ $exam->exists ? 'Ubah ujian' : 'Buat ujian baru' }}</h1><p>Atur jadwal, durasi, dan pertanyaan pilihan ganda.</p></div></div>
@if ($errors->any())<div class="notice notice-error">Periksa kembali data yang dimasukkan. {{ $errors->first() }}</div>@endif
@if ($subjects->isEmpty())<div class="notice notice-error">Tambahkan mata pelajaran terlebih dahulu sebelum membuat ujian. <a href="{{ route('subjects.index') }}">Kelola mata pelajaran</a></div>@else
<form method="POST" action="{{ $exam->exists ? route('exams.update', $exam) : route('exams.store') }}" class="exam-editor">
    @csrf
    @if ($exam->exists)@method('PUT')@endif
    <section class="surface editor-section"><div class="surface-heading"><div><span class="eyebrow eyebrow-green">DETAIL UJIAN</span><h2>Informasi dasar</h2></div></div>
        <div class="field-grid">
            <label class="field field-wide">Judul ujian<input name="title" value="{{ old('title', $exam->title) }}" maxlength="160" required placeholder="Contoh: Penilaian Akhir Semester Matematika"></label>
            <label class="field">Mata pelajaran<select name="subject_id" required><option value="">Pilih mata pelajaran</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $exam->subject_id) == $subject->id)>{{ $subject->name }} · Kelas {{ $subject->grade_level }}</option>@endforeach</select></label>
            <label class="field">Durasi (menit)<input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?: 60) }}" min="1" max="300" required></label>
            <label class="field">Mulai<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $exam->starts_at?->format('Y-m-d\TH:i')) }}"></label>
            <label class="field">Selesai<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $exam->ends_at?->format('Y-m-d\TH:i')) }}"></label>
            <label class="field">Nilai minimum lulus<input type="number" name="passing_score" value="{{ old('passing_score', $exam->passing_score ?? 75) }}" min="0" max="100" required></label>
            <label class="field">Maksimal percobaan<select name="max_attempts">@foreach ([1, 2, 3, 4, 5] as $attemptLimit)<option value="{{ $attemptLimit }}" @selected(old('max_attempts', $exam->max_attempts ?? 1) == $attemptLimit)>{{ $attemptLimit }} kali</option>@endforeach</select></label>
            <label class="field field-wide">Instruksi<textarea name="instructions" rows="3" maxlength="5000" placeholder="Petunjuk singkat untuk siswa">{{ old('instructions', $exam->instructions) }}</textarea></label>
            <label class="check-field"><input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $exam->shuffle_questions))><span>Acak urutan soal untuk setiap siswa</span></label>
            <label class="check-field"><input type="checkbox" name="show_score" value="1" @checked(old('show_score', $exam->show_score ?? true))><span>Tampilkan nilai setelah dikumpulkan</span></label>
            <label class="field">Status<select name="status"><option value="draft" @selected(old('status', $exam->status ?: 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $exam->status) === 'published')>Terbitkan</option></select></label>
        </div>
    </section>
    <section class="surface editor-section question-editor"><div class="surface-heading"><div><span class="eyebrow eyebrow-green">BANK SOAL</span><h2>Pertanyaan</h2></div><button class="outline-button" type="button" id="add-question">+ Tambah soal</button></div>
        <div id="question-list">
            @foreach (old('questions', $questionRows) as $index => $question)
                @php
                    $questionOptions = is_array($question) ? ($question['options'] ?? []) : ($question?->options ?? []);
                    $questionPrompt = is_array($question) ? ($question['prompt'] ?? '') : ($question?->prompt ?? '');
                    $correctOption = is_array($question) ? ($question['correct_option'] ?? 'A') : ($question?->correct_option ?? 'A');
                    $questionPoints = is_array($question) ? ($question['points'] ?? 1) : ($question?->points ?? 1);
                @endphp
                <fieldset class="question-block"><legend>Soal <span class="question-number">{{ $index + 1 }}</span></legend><button type="button" class="remove-question" aria-label="Hapus soal">×</button>
                    <label class="field field-wide">Pertanyaan<textarea name="questions[{{ $index }}][prompt]" rows="3" required>{{ $questionPrompt }}</textarea></label>
                    <div class="option-grid">@foreach (['A', 'B', 'C', 'D'] as $letter)<label class="field">Pilihan {{ $letter }}<input name="questions[{{ $index }}][options][{{ $letter }}]" value="{{ $questionOptions[$letter] ?? '' }}" required></label>@endforeach</div>
                    <div class="question-meta"><label class="field">Kunci jawaban<select name="questions[{{ $index }}][correct_option]">@foreach (['A', 'B', 'C', 'D'] as $letter)<option value="{{ $letter }}" @selected($correctOption === $letter)>{{ $letter }}</option>@endforeach</select></label><label class="field">Poin<input type="number" name="questions[{{ $index }}][points]" value="{{ $questionPoints }}" min="1" max="100" required></label></div>
                </fieldset>
            @endforeach
        </div>
    </section>
    <div class="editor-actions"><a class="text-action" href="{{ route('exams.index') }}">Batal</a><button class="action-button" type="submit">Simpan ujian <span aria-hidden="true">&rarr;</span></button></div>
</form>
<template id="question-template"><fieldset class="question-block"><legend>Soal <span class="question-number"></span></legend><button type="button" class="remove-question" aria-label="Hapus soal">×</button><label class="field field-wide">Pertanyaan<textarea data-name="questions[__INDEX__][prompt]" rows="3" required></textarea></label><div class="option-grid">@foreach (['A', 'B', 'C', 'D'] as $letter)<label class="field">Pilihan {{ $letter }}<input data-name="questions[__INDEX__][options][{{ $letter }}]" required></label>@endforeach</div><div class="question-meta"><label class="field">Kunci jawaban<select data-name="questions[__INDEX__][correct_option]">@foreach (['A', 'B', 'C', 'D'] as $letter)<option value="{{ $letter }}">{{ $letter }}</option>@endforeach</select></label><label class="field">Poin<input type="number" data-name="questions[__INDEX__][points]" value="1" min="1" max="100" required></label></div></fieldset></template>
<script>
    const questionList = document.querySelector('#question-list');
    const questionTemplate = document.querySelector('#question-template');
    const renumberQuestions = () => [...questionList.querySelectorAll('.question-block')].forEach((block, index) => {
        block.querySelector('.question-number').textContent = index + 1;
        block.querySelectorAll('[data-name]').forEach((input) => input.name = input.dataset.name.replaceAll('__INDEX__', index));
        block.querySelectorAll('[name]').forEach((input) => input.name = input.name.replace(/questions\[\d+\]/g, `questions[${index}]`));
    });
    document.querySelector('#add-question').addEventListener('click', () => { questionList.append(questionTemplate.content.cloneNode(true)); renumberQuestions(); });
    questionList.addEventListener('click', (event) => { if (event.target.closest('.remove-question') && questionList.querySelectorAll('.question-block').length > 1) { event.target.closest('.question-block').remove(); renumberQuestions(); } });
    renumberQuestions();
</script>
@endif
@endsection