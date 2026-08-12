# fkipapp

Aplikasi rekap ujian proposal, seminar hasil penelitian, dan sidang skripsi, sekaligus perhitungan dan pelaporan honor pembimbing/penguji untuk **FKIP Universitas Siliwangi**.

Dibangun dengan [Laravel 10](https://laravel.com), [Yajra DataTables](https://yajra-datatables.readthedocs.io/), dan [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission).

## Fitur Utama

- **Pendaftaran ujian** — pencatatan pendaftaran ujian proposal, seminar hasil, dan sidang skripsi mahasiswa beserta pembimbing dan penguji.
- **Rekap & laporan ujian** — laporan ujian per jurusan, per tanggal, dan per penguji, lengkap dengan status pelaporan dan konfirmasi sidang.
- **Perhitungan honor** — perhitungan otomatis honor pembimbing dan penguji (proposal, seminar, skripsi) beserta potongan pajak berdasarkan golongan dan status kepegawaian dosen.
- **Laporan pembayaran honor** — rekap honor per periode, status ASN/NON ASN, dan status pembayaran per dosen untuk bagian keuangan.
- **Manajemen data master** — data mahasiswa, dosen, jurusan, dan pengguna aplikasi.
- **Hak akses berbasis peran** — peran `admin`, `jurusan`, `keuangan`, dan `dekanat` dengan cakupan menu dan data yang berbeda-beda.

## Teknologi

- PHP 8.1+ dan Laravel 10
- MySQL
- Bootstrap 5, Sass, Vite
- Yajra DataTables untuk tabel data interaktif
- Spatie Laravel Permission untuk role & permission

## Instalasi

```bash
git clone <url-repo-ini> fkipapp
cd fkipapp

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Atur koneksi database pada `.env` (default menggunakan MySQL dengan nama basis data `db_fkipapp`), lalu jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Seeder awal akan membuat akun admin (`admin` / `asdfasdf`), peran (`admin`, `jurusan`, `keuangan`, `dekanat`), serta data master jurusan, dosen, mahasiswa, dan akun operator jurusan dari berkas CSV di `database/seeders/csvs`.

Jalankan aplikasi untuk pengembangan:

```bash
php artisan serve
npm run dev
```

## Menjalankan Test

```bash
php artisan test
```

## Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
