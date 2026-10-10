<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7f3">
    <title>Dashboard | CBT Syahidah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cbt.css') }}">
</head>
<body class="dashboard-page">
    <div class="app-frame">
        <aside class="sidebar">
            <a class="brand brand-dark" href="{{ route('dashboard') }}">
                <span class="brand-mark">S</span>
                <span><strong>SYAHIDAH</strong><small>PORTAL AKADEMIK</small></span>
            </a>
            <div class="nav-caption">MENU UTAMA</div>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a class="nav-link nav-link-active" href="{{ route('dashboard') }}"><span class="nav-icon">▦</span>Ringkasan</a>
                <a class="nav-link" href="{{ route('results.index') }}"><span class="nav-icon">◍</span>Riwayat</a>
                <a class="nav-link" href="{{ route('leaderboard.index') }}"><span class="nav-icon">◆</span>Peringkat</a>
                @if ($user->isAdmin() || $user->isTeacher())
                    <a class="nav-link" href="{{ route('reports.index') }}"><span class="nav-icon">▣</span>Analitik</a>
                @endif
                <a class="nav-link" href="{{ route('exams.index') }}"><span class="nav-icon">▤</span>Ujian</a>
                @if ($user->isAdmin() || $user->isTeacher())
                    <a class="nav-link" href="{{ route('subjects.index') }}"><span class="nav-icon">◈</span>Mata pelajaran</a>
                @endif
                @if ($user->isAdmin())
                    <a class="nav-link" href="{{ route('users.index') }}"><span class="nav-icon">♙</span>Pengguna</a>
                @endif
            </nav>
            <div class="sidebar-bottom">
                <div class="school-note"><span class="school-note-mark">S</span><div><strong>SMP Syahidah</strong><small>Tahun ajaran {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</small></div></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit"><span aria-hidden="true">↪</span> Keluar</button>
                </form>
            </div>
        </aside>

        <main class="dashboard-main">
            <header class="topbar">
                <div class="breadcrumb">Portal <span>/</span> Ringkasan</div>
                <div class="topbar-user"><span class="topbar-date">{{ now()->translatedFormat('l, d F Y') }}</span><span class="avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><span class="topbar-name">{{ $user->name }}<small>{{ $roleLabel }}</small></span></div>
            </header>

            <div class="dashboard-content" id="overview">
                <section class="welcome-row">
                    <div><span class="eyebrow eyebrow-green">{{ $roleLabel }} · SMP SYARIF HIDAYATULLAH</span><h1>Assalamu'alaikum, {{ explode(' ', $user->name)[0] }}.</h1><p>Semoga harimu produktif. Berikut ringkasan aktivitas akademik hari ini.</p></div>
                    <div class="today-mark"><span>{{ now()->format('d') }}</span><small>{{ now()->translatedFormat('M Y') }}</small></div>
                </section>

                <section class="stat-grid" aria-label="Statistik akademik">
                    @foreach ($stats as $index => $stat)
                        <article class="stat-card stat-{{ $stat['tone'] }}">
                            <div class="stat-top"><span>{{ $stat['label'] }}</span><span class="stat-symbol">{{ ['↗', '◌', '▧', '✳'][$index % 4] }}</span></div>
                            <strong>{{ number_format($stat['value'], 0, ',', '.') }}</strong>
                            <small>{{ $user->isStudent() ? 'Data sekolah' : 'Terdata di portal' }}</small>
                        </article>
                    @endforeach
                </section>

                <div class="content-columns">
                    <section class="panel exam-panel" id="upcoming">
                        <div class="panel-heading"><div><span class="eyebrow eyebrow-green">KALENDER AKADEMIK</span><h2>Ujian mendatang</h2></div><span class="panel-count">{{ $upcomingExams->count() }} ujian</span></div>
                        @if ($upcomingExams->isNotEmpty())
                            <div class="exam-table-wrap"><table class="exam-table"><thead><tr><th>Ujian</th><th>Mata pelajaran</th><th>Durasi</th><th>Status</th></tr></thead><tbody>
                                @foreach ($upcomingExams as $exam)
                                    <tr><td><strong>{{ $exam->title }}</strong><small>{{ $exam->starts_at?->translatedFormat('d M Y, H:i') ?? 'Waktu belum ditentukan' }}</small></td><td>{{ $exam->subject->name }}</td><td>{{ $exam->duration_minutes }} menit</td><td><span class="status-pill">{{ $exam->starts_at && $exam->starts_at->isFuture() ? 'Terjadwal' : 'Tersedia' }}</span></td></tr>
                                @endforeach
                            </tbody></table></div>
                        @else
                            <div class="empty-state"><span class="empty-icon">◷</span><strong>Belum ada ujian terjadwal</strong><p>Ujian yang sudah diterbitkan akan muncul di sini.</p></div>
                        @endif
                    </section>
                    <aside class="panel note-panel">
                        <span class="eyebrow eyebrow-green">INFORMASI</span>
                        <div class="note-illustration" aria-hidden="true"><span class="note-sun"></span><span class="note-line note-line-one"></span><span class="note-line note-line-two"></span><span class="note-book">✦</span></div>
                        <h2>Siap untuk tantangan berikutnya?</h2>
                        <p>Pastikan informasi akunmu tetap terbarui. Jika ada kendala, administrator sekolah siap membantu.</p>
                        <span class="note-signoff">SMP SYARIF HIDAYATULLAH</span>
                    </aside>
                </div>
                <footer class="dashboard-footer"><span>CBT Syahidah</span><span>Belajar jujur, hasil bermakna.</span></footer>
            </div>
        </main>
    </div>
</body>
</html>