@extends('layouts.portal')

@section('title', 'Riwayat ujian')
@section('breadcrumb', 'Riwayat ujian')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow eyebrow-green">{{ auth()->user()->isStudent() ? 'PROGRES SISWA' : 'REKAP HASIL' }}</span>
        <h1>Riwayat ujian</h1>
        <p>{{ auth()->user()->isStudent() ? 'Semua ujian yang sudah kamu selesaikan dan hasilnya.' : 'Ringkasan performa peserta didik berdasarkan ujian yang sudah dikumpulkan.' }}</p>
    </div>
</div>

<section class="stat-grid" aria-label="Ringkasan hasil">
    <article class="stat-card stat-mint">
        <div class="stat-top"><span>Ujian selesai</span><span class="stat-symbol">✦</span></div>
        <strong>{{ number_format($summary['total'], 0, ',', '.') }}</strong>
        <small>Data valid</small>
    </article>
    <article class="stat-card stat-blue">
        <div class="stat-top"><span>Rata-rata nilai</span><span class="stat-symbol">↗</span></div>
        <strong>{{ $summary['average_score'] }}</strong>
        <small>Skor rata-rata</small>
    </article>
    <article class="stat-card stat-peach">
        <div class="stat-top"><span>Skor tertinggi</span><span class="stat-symbol">◌</span></div>
        <strong>{{ $summary['best_score'] }}</strong>
        <small>Nilai terbaik</small>
    </article>
    <article class="stat-card stat-yellow">
        <div class="stat-top"><span>Tingkat kelulusan</span><span class="stat-symbol">✓</span></div>
        <strong>{{ $summary['pass_rate'] }}%</strong>
        <small>Memenuhi passing grade</small>
    </article>
</section>

<section class="surface exam-list-surface">
    <div class="exam-list-head"><span>{{ $attempts->total() }} hasil</span><span>Terbaru</span></div>
    <div class="exam-list">
        @forelse ($attempts as $attempt)
            <article class="exam-list-row">
                <div class="exam-subject-mark">{{ mb_strtoupper(mb_substr($attempt->exam->subject->name, 0, 1)) }}</div>
                <div class="exam-list-info">
                    <div class="exam-title-line">
                        <h2>{{ $attempt->exam->title }}</h2>
                        <span class="status-pill" style="background: {{ $attempt->score >= ($attempt->exam->passing_score ?? 0) ? '#eaf3eb' : '#fff3ee' }}; color: {{ $attempt->score >= ($attempt->exam->passing_score ?? 0) ? '#2a5e46' : '#9b4a37' }};">
                            {{ $attempt->score >= ($attempt->exam->passing_score ?? 0) ? 'Lulus' : 'Perlu perbaikan' }}
                        </span>
                    </div>
                    <p>
                        {{ $attempt->exam->subject->name }} · Kelas {{ $attempt->exam->subject->grade_level }} · {{ $attempt->exam->duration_minutes }} menit
                        @if (! auth()->user()->isStudent())
                            · {{ $attempt->user?->name ?? 'Peserta' }}
                        @endif
                    </p>
                    <small>{{ $attempt->submitted_at?->translatedFormat('d M Y, H:i') ?? 'Waktu selesai tidak tersedia' }}</small>
                </div>
                <div class="exam-list-action">
                    <span class="status-pill" style="background:#edf3f2;color:#163d2f;">{{ $attempt->score }} / 100</span>
                    <a class="small-action" href="{{ route('attempts.result', $attempt) }}">Lihat detail</a>
                </div>
            </article>
        @empty
            <div class="blank-state blank-large">
                <span class="empty-icon">◎</span>
                <strong>Belum ada ujian yang selesai</strong>
                <p>{{ auth()->user()->isStudent() ? 'Setelah kamu mengumpulkan ujian, hasilnya akan muncul di sini.' : 'Hasil ujian siswa akan muncul di sini setelah pengumpulan.' }}</p>
            </div>
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $attempts->links() }}</div>
</section>
@endsection
