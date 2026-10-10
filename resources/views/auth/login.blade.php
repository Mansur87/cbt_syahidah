<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7f3">
    <title>Masuk | CBT Syahidah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cbt.css') }}">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-story" aria-label="SMP Syarif Hidayatullah">
            <a class="brand brand-light" href="{{ route('login') }}">
                <span class="brand-mark">S</span>
                <span><strong>SYAHIDAH</strong><small>SEKOLAH MENENGAH PERTAMA</small></span>
            </a>
            <div class="story-copy">
                <span class="eyebrow">RUANG BELAJAR DIGITAL</span>
                <h1>Setiap usaha<br>punya <em>arti.</em></h1>
                <p>Masuk ke ruang ujian untuk melanjutkan langkah belajarmu bersama SMP Syarif Hidayatullah.</p>
                <div class="story-metrics" aria-label="Statistik portal">
                    <div>
                        <strong>24/7</strong>
                        <span>Akses ujian</span>
                    </div>
                    <div>
                        <strong>100%</strong>
                        <span>Akuntabel</span>
                    </div>
                    <div>
                        <strong>3</strong>
                        <span>Peran aktif</span>
                    </div>
                </div>
            </div>
            <div class="story-footer"><span class="story-dot"></span> Tumbuh dengan ilmu, melangkah dengan yakin</div>
            <span class="story-number" aria-hidden="true">01</span>
        </section>

        <section class="login-form-panel">
            <div class="form-topline"><span>PORTAL AKADEMIK</span><span>TAHUN AJARAN {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</span></div>
            <div class="login-form-wrap">
                <span class="eyebrow eyebrow-green">SELAMAT DATANG KEMBALI</span>
                <h2>Masuk ke akunmu</h2>
                <p class="form-intro">Gunakan akun yang diberikan oleh pihak sekolah.</p>

                @if ($errors->any())
                    <div class="form-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="login-form">
                    @csrf
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" required autofocus>

                    <div class="label-row"><label for="password">Kata sandi</label></div>
                    <input id="password" name="password" type="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>

                    <label class="remember-option"><input type="checkbox" name="remember" value="1"><span>Ingat saya di perangkat ini</span></label>
                    <button class="button button-primary button-full" type="submit">Masuk ke portal <span aria-hidden="true">&rarr;</span></button>
                </form>
                <p class="login-help">Butuh bantuan akses? <span>Hubungi administrator sekolah.</span></p>
            </div>
            <footer class="form-footer"><span>&copy; {{ now()->year }} SMP Syarif Hidayatullah</span><span>Portal CBT</span></footer>
        </section>
    </main>
</body>
</html>