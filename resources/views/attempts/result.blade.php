@extends('layouts.portal')

@section('title', 'Hasil ujian')
@section('breadcrumb', 'Hasil')

@section('content')
<section class="result-wrap">
    <span class="eyebrow eyebrow-green">HASIL PENILAIAN</span><div class="result-medallion">{{ $attempt->exam->show_score ? '✳' : '✓' }}</div>
    <h1>{{ $attempt->exam->show_score ? ($attempt->score >= $attempt->exam->passing_score ? 'Selamat, kamu lulus.' : 'Tetap semangat belajar.') : 'Jawaban berhasil dikumpulkan.' }}</h1>
    <p class="result-exam-title">{{ $attempt->exam->title }} · {{ $attempt->exam->subject->name }}</p>
    @if ($attempt->exam->show_score)<div class="score-display"><strong>{{ $attempt->score }}</strong><span>/ 100</span></div><span class="result-status {{ $attempt->score >= $attempt->exam->passing_score ? 'passed' : 'not-passed' }}">{{ $attempt->score >= $attempt->exam->passing_score ? 'Memenuhi nilai minimum' : 'Belum mencapai nilai minimum' }} · {{ $attempt->exam->passing_score }}</span>@else<p class="score-hidden">Nilai akan tersedia setelah guru menyelesaikan penilaian.</p>@endif
    <div class="result-meta"><span>Mulai<strong>{{ $attempt->started_at->translatedFormat('d M Y, H:i') }}</strong></span><span>Dikumpulkan<strong>{{ $attempt->submitted_at->translatedFormat('d M Y, H:i') }}</strong></span></div>
    <a class="action-button" href="{{ route('exams.index') }}">Kembali ke daftar ujian <span aria-hidden="true">&rarr;</span></a>
</section>
@endsection