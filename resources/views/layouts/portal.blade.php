<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | CBT Syahidah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cbt.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operations.css') }}">
</head>
<body class="dashboard-page">
    <div class="app-frame">
        <aside class="sidebar">
            <a class="brand brand-dark" href="{{ route('dashboard') }}"><span class="brand-mark">S</span><span><strong>SYAHIDAH</strong><small>PORTAL AKADEMIK</small></span></a>
            <div class="nav-caption">MENU UTAMA</div>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a class="nav-link" href="{{ route('dashboard') }}"><span class="nav-icon">▦</span>Ringkasan</a>
                <a class="nav-link {{ request()->routeIs('results.*') ? 'nav-link-active' : '' }}" href="{{ route('results.index') }}"><span class="nav-icon">◍</span>Riwayat</a>
                <a class="nav-link {{ request()->routeIs('leaderboard.*') ? 'nav-link-active' : '' }}" href="{{ route('leaderboard.index') }}"><span class="nav-icon">◆</span>Peringkat</a>
                @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'nav-link-active' : '' }}" href="{{ route('reports.index') }}"><span class="nav-icon">▣</span>Analitik</a>
                @endif
                <a class="nav-link {{ request()->routeIs('exams.*') ? 'nav-link-active' : '' }}" href="{{ route('exams.index') }}"><span class="nav-icon">▤</span>Ujian</a>
                @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <a class="nav-link {{ request()->routeIs('subjects.*') ? 'nav-link-active' : '' }}" href="{{ route('subjects.index') }}"><span class="nav-icon">◈</span>Mata pelajaran</a>
                @endif
                @if (auth()->user()->isAdmin())
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}" href="{{ route('users.index') }}"><span class="nav-icon">♙</span>Pengguna</a>
                @endif
            </nav>
            <div class="sidebar-bottom">
                <div class="school-note"><span class="school-note-mark">S</span><div><strong>SMP Syahidah</strong><small>Tahun ajaran {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</small></div></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit"><span aria-hidden="true">↪</span> Keluar</button></form>
            </div>
        </aside>
        <main class="dashboard-main">
            <header class="topbar">
                <div class="breadcrumb"><a href="{{ route('dashboard') }}">Portal</a><span>/</span>@yield('breadcrumb')</div>
                <div class="topbar-user"><span class="topbar-date">{{ now()->translatedFormat('l, d F Y') }}</span><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span class="topbar-name">{{ auth()->user()->name }}<small>{{ auth()->user()->isAdmin() ? 'Administrator' : (auth()->user()->isTeacher() ? 'Guru' : 'Siswa') }}</small></span></div>
            </header>
            <div class="page-content">@yield('content')</div>
        </main>
    </div>
</body>
</html>