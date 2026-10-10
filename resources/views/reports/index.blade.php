@extends('layouts.portal')

@section('title', 'Analitik ujian')
@section('breadcrumb', 'Analitik ujian')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow eyebrow-green">LAPORAN GURU</span>
        <h1>Analitik ujian</h1>
        <p>Performa mata pelajaran, tingkat kelulusan, dan capaian ujian yang sudah dikumpulkan.</p>
    </div>
</div>

<section class="surface exam-list-surface">
    <div class="exam-list-head"><span>{{ $subjects->count() }} mata pelajaran</span><span>Ringkasan</span></div>
    <div class="exam-list">
        @forelse ($subjects as $subject)
            <article class="exam-list-row">
                <div class="exam-subject-mark">{{ mb_strtoupper(mb_substr($subject['name'], 0, 1)) }}</div>
                <div class="exam-list-info">
                    <div class="exam-title-line">
                        <h2>{{ $subject['name'] }}</h2>
                        <span class="status-pill" style="background:#eaf3eb;color:#2a5e46;">{{ $subject['code'] }}</span>
                    </div>
                    <p>Kelas {{ $subject['grade_level'] }} · Guru: {{ $subject['teacher'] }} · {{ $subject['exam_count'] }} ujian</p>
                    <small>{{ $subject['student_count'] }} siswa mengikuti ujian · rata-rata {{ $subject['average_score'] }}</small>
                </div>
                <div class="exam-list-action">
                    <span class="status-pill" style="background:#edf3f2;color:#163d2f;">{{ $subject['pass_rate'] }}% lulus</span>
                </div>
            </article>

            @if ($subject['exams']->isNotEmpty())
                <div class="surface" style="margin:0 0 14px; padding:14px 16px; background:#f9faf9;">
                    @foreach ($subject['exams'] as $exam)
                        <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; padding:8px 0; border-bottom:1px solid #edf1ee;">
                            <div>
                                <strong style="display:block; font-size:11px; color:#1d3029;">{{ $exam['title'] }}</strong>
                                <small style="color:#718078;">{{ $exam['attempts'] }} pengerjaan · rata-rata {{ $exam['average_score'] }}</small>
                            </div>
                            <span class="status-pill" style="background:{{ $exam['pass_rate'] >= 70 ? '#eaf3eb' : '#fff3ee' }}; color:{{ $exam['pass_rate'] >= 70 ? '#2a5e46' : '#9b4a37' }};">{{ $exam['pass_rate'] }}% lulus</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @empty
            <div class="blank-state blank-large">
                <span class="empty-icon">▣</span>
                <strong>Belum ada data analitik</strong>
                <p>Setelah ujian diterbitkan dan dikumpulkan, laporan mata pelajaran akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
