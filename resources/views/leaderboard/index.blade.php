@extends('layouts.portal')

@section('title', 'Papan peringkat')
@section('breadcrumb', 'Papan peringkat')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow eyebrow-green">{{ auth()->user()->isStudent() ? 'RANKING KELAS' : 'MONITORING KINERJA' }}</span>
        <h1>Papan peringkat</h1>
        <p>{{ auth()->user()->isStudent() ? 'Posisi kamu dibandingkan teman sekelas berdasarkan rata-rata ujian.' : 'Kinerja siswa berdasarkan rata-rata skor tertinggi yang sudah dikumpulkan.' }}</p>
    </div>
</div>

<section class="stat-grid" aria-label="Ringkasan papan peringkat">
    <article class="stat-card stat-mint">
        <div class="stat-top"><span>Siswa terdata</span><span class="stat-symbol">◍</span></div>
        <strong>{{ number_format($summary['students'], 0, ',', '.') }}</strong>
        <small>Peserta aktif</small>
    </article>
    <article class="stat-card stat-blue">
        <div class="stat-top"><span>Skor tertinggi</span><span class="stat-symbol">↗</span></div>
        <strong>{{ $summary['highest_score'] }}</strong>
        <small>Rata-rata terbaik</small>
    </article>
    <article class="stat-card stat-peach">
        <div class="stat-top"><span>Rata-rata kelas</span><span class="stat-symbol">▧</span></div>
        <strong>{{ $summary['average_score'] }}</strong>
        <small>Nilai rata-rata</small>
    </article>
    @if (auth()->user()->isStudent())
        <article class="stat-card stat-yellow">
            <div class="stat-top"><span>Posisimu</span><span class="stat-symbol">★</span></div>
            <strong>#{{ $summary['my_rank'] ?? '-' }}</strong>
            <small>Ranking saat ini</small>
        </article>
    @endif
</section>

<section class="surface exam-list-surface">
    <div class="exam-list-head"><span>{{ count($ranked) }} peserta</span><span>Skor rata-rata</span></div>
    <div class="exam-list">
        @forelse ($ranked as $index => $entry)
            <article class="exam-list-row">
                <div class="exam-subject-mark" style="background: {{ $index === 0 ? '#f4e9c9' : ($index === 1 ? '#edf3f2' : '#eef3f0') }}; color: {{ $index === 0 ? '#765d1c' : '#2b4d41' }};">{{ $index + 1 }}</div>
                <div class="exam-list-info">
                    <div class="exam-title-line">
                        <h2>{{ $entry['user']->name }}</h2>
                        <span class="status-pill" style="background: {{ $index === 0 ? '#f4e9c9' : '#eaf3eb' }}; color: {{ $index === 0 ? '#6d5416' : '#2a5e46' }};">{{ $index === 0 ? 'Juara' : 'Peringkat '.($index + 1) }}</span>
                    </div>
                    <p>{{ $entry['user']->class_name ?? 'Kelas '.($entry['user']->grade_level ?? '-') }} · {{ $entry['total_attempts'] }} ujian tercatat</p>
                    <small>Skor terbaik {{ $entry['best_score'] }} · terakhir {{ optional($entry['last_submission'])->translatedFormat('d M Y, H:i') ?? 'Belum tersedia' }}</small>
                </div>
                <div class="exam-list-action">
                    <span class="status-pill" style="background:#edf3f2;color:#163d2f;">{{ $entry['average_score'] }}</span>
                </div>
            </article>
        @empty
            <div class="blank-state blank-large">
                <span class="empty-icon">◇</span>
                <strong>Belum ada data peringkat</strong>
                <p>Setelah siswa mengumpulkan ujian, ranking akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
