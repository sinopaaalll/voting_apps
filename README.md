# SuaraKita

Aplikasi voting internal sederhana berbasis Laravel 12. Employee masuk menggunakan NIK dan hanya dapat memilih satu kali. Admin mengelola employee, kandidat, dan melihat hasil agregat.

Antarmuka pemilih dirancang mobile-first untuk proses voting melalui ponsel.

## Menjalankan aplikasi

```bash
composer install
npm install
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

Halaman pemilih tersedia di `/login`, sedangkan halaman admin tersedia di `/admin/login`.

Data contoh hasil seeding:

- `EMP-001` — Andi Pratama
- `EMP-002` — Siti Rahma

Kredensial admin pengembangan awal:

- Username: `admin`
- Password: `admin123`

Ganti password sebelum aplikasi digunakan. Buat hash baru melalui Laravel Tinker dengan `Hash::make('password-baru')`, kemudian simpan hasilnya pada `ADMIN_PASSWORD_HASH` di `.env` menggunakan tanda kutip tunggal.

## Database

Aplikasi menggunakan tiga tabel domain:

- `employee`
- `kandidat`
- `voting`

Laravel juga membuat tabel internal `migrations` untuk mencatat migration yang sudah dijalankan. Session dan cache menggunakan file, sedangkan queue menggunakan driver sync sehingga tidak membutuhkan tabel tambahan.

## Import employee

Admin dapat mengimpor file XLSX, XLS, atau CSV melalui halaman Employee tanpa batas jumlah baris atau ukuran file dari aplikasi. XLSX dan CSV diproses secara streaming dan disimpan dengan bulk upsert per batch agar penggunaan memori serta jumlah query tetap rendah. Unduh template yang tersedia pada dialog Import Excel dan gunakan header:

`nik`, `name`, `department`, `employment_status`, `position`

Header Indonesia `nama`, `departemen`, `status_karyawan`, dan `jabatan` juga didukung. Status hanya boleh berisi `tetap`, `kontrak`, atau `magang`. NIK baru akan ditambahkan dan NIK yang sudah ada akan diperbarui. Seluruh file divalidasi terlebih dahulu; bila satu baris bermasalah, tidak ada data yang diimpor.

Checkbox pada header tabel memilih seluruh employee yang dapat dihapus pada halaman aktif. Employee yang sudah voting tetap dilindungi dan tidak bisa dihapus.

## Verifikasi

```bash
php artisan test
vendor/bin/pint --test
npm run build
```
