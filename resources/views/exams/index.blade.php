@extends('layouts.portal')

@section('title', 'Ujian')
@section('breadcrumb', 'Ujian')

@section('content')
<div class="page-heading"><div><span class="eyebrow eyebrow-green">PENILAIAN AKADEMIK</span><h1>Ujian</h1><p>{{ auth()->user()->isStudent() ? 'Daftar ujian yang tersedia untukmu.' : 'Buat dan kelola ujian untuk siswa.' }}</p></div>@if (auth()->user()->isAdmin() || auth()->user()->isTeacher())<a class="action-button" href="{{ route('exams.create') }}">Buat ujian <span aria-hidden="true">+</span></a>@endif</div>
@if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
<section class="surface exam-list-surface">
    <div class="exam-list-head"><span>{{ $exams->total() }} ujian</span><span>Urut terbaru</span></div>
    <div class="exam-list">
        @forelse ($exams as $exam)
            @php($attempt = $attempts->get($exam->id))
            <article class="exam-list-row">
                <div class="exam-subject-mark">{{ mb_strtoupper(mb_substr($exam->subject->name, 0, 1)) }}</div>
                <div class="exam-list-info"><div class="exam-title-line"><h2>{{ $exam->title }}</h2><span class="exam-status exam-status-{{ $exam->status }}">{{ $exam->status === 'published' ? 'Diterbitkan' : 'Draft' }}</span></div><p>{{ $exam->subject->name }} · Kelas {{ $exam->subject->grade_level }} · {{ $exam->questions_count }} soal</p><small>{{ $exam->starts_at?->translatedFormat('d M Y, H:i') ?? 'Waktu fleksibel' }} · {{ $exam->duration_minutes }} menit</small></div>
                @if (auth()->user()->isStudent())
                    <div class="exam-list-action">
                        @if ($attempt?->status === 'in_progress')<a class="small-action" href="{{ route('attempts.show', $attempt) }}">Lanjutkan <span aria-hidden="true">&rarr;</span></a>
                        @elseif ($attempt?->status === 'submitted' && $attempt->attempt_number < $exam->max_attempts)<div class="retry-actions"><a class="small-action small-action-muted" href="{{ route('attempts.result', $attempt) }}">Hasil</a><form method="POST" action="{{ route('exams.start', $exam) }}">@csrf<button class="small-action" type="submit">Coba lagi</button></form></div>
                        @elseif ($attempt?->status === 'submitted')<a class="small-action small-action-muted" href="{{ route('attempts.result', $attempt) }}">Lihat hasil</a>
                        @else<form method="POST" action="{{ route('exams.start', $exam) }}">@csrf<button class="small-action" type="submit">Mulai ujian <span aria-hidden="true">&rarr;</span></button></form>@endif
                    </div>
                @else
                    <div class="exam-list-action"><a class="small-action small-action-muted" href="{{ route('exams.edit', $exam) }}">Kelola <span aria-hidden="true">&rarr;</span></a></div>
                @endif
            </article>
        @empty
            <div class="blank-state blank-large"><span class="empty-icon">▤</span><strong>Belum ada ujian</strong><p>{{ auth()->user()->isStudent() ? 'Ujian yang diterbitkan sekolah akan muncul di sini.' : 'Mulai dengan membuat ujian pertama.' }}</p></div>
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $exams->links() }}</div>
</section>
@endsection