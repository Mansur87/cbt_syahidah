# CBT Syahidah

Portal ujian berbasis komputer untuk SMP Syarif Hidayatullah, dibangun dengan Laravel 13 dan MariaDB.

## Fondasi saat ini

- Login menggunakan username dan kata sandi, dengan sesi Laravel dan regenerasi sesi.
- Peran akun `admin`, `teacher`, dan `student`; pembuatan akun tidak tersedia untuk publik.
- Dashboard ringkas sesuai peran, dengan statistik sekolah dan daftar ujian terbit.
- Struktur awal mata pelajaran dan ujian, termasuk jadwal, durasi, status, dan pengaturan nilai.

## Menjalankan aplikasi

1. Siapkan PHP 8.3+, Composer, Node.js, dan MariaDB. Ekstensi PHP `pdo_mysql` wajib tersedia.
2. Salin `.env.example` menjadi `.env`, lalu isi koneksi database dan akun administrator awal:

	```dotenv
	DB_CONNECTION=mysql
	DB_HOST=127.0.0.1
	DB_PORT=3306
	DB_DATABASE=cbt_syahidah
	DB_USERNAME=root
	DB_PASSWORD=kata-sandi-database
	CBT_ADMIN_EMAIL=admin@syahidah.sch.id
	CBT_ADMIN_USERNAME=admin
	CBT_ADMIN_PASSWORD=ganti-dengan-kata-sandi-kuat
	```

3. Buat database `cbt_syahidah`, lalu jalankan:

	```sh
	composer install
	php artisan key:generate
	php artisan migrate --seed
	npm install
	npm run build
	php artisan serve
	```

4. Buka `http://127.0.0.1:8000` dan masuk menggunakan username serta kata sandi administrator yang dikonfigurasi. Username akun lama dihasilkan dari bagian alamat email sebelum `@`; karakter pemisah dinormalisasi dan angka ditambahkan jika username sudah dipakai.

Untuk pengujian otomatis, jalankan `php artisan test`. Lingkungan pengujian menggunakan SQLite in-memory.
