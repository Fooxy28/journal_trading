# JournalTrading

Refactor struktur proyek untuk clean code dan clean architecture ringan (tanpa framework).

## Struktur Folder

- `index.php` : UI utama (frontend).
- `ajax_handler.php` : API entrypoint minimal.
- `assets/js/app.js` : logika frontend JavaScript.
- `app/bootstrap.php` : bootstrap dependency backend.
- `app/Http/AjaxController.php` : HTTP action controller.
- `app/Services/TradingService.php` : business logic + query database.
- `app/Support/JsonResponse.php` : response JSON standar.
- `config/database.php` : factory koneksi PDO (support env).
- `docs/` : dokumen non-kode (plan/desain).
- `trading_journal_db.sql` : dump schema dan data contoh.

## Prinsip yang Diterapkan

1. **Single Responsibility**
   - `ajax_handler.php` hanya routing request/response.
   - SQL dan aturan bisnis dipindahkan ke `TradingService`.

2. **Separation of Concerns**
   - Entry API dipisah dari controller (`ajax_handler.php` -> `AjaxController`).
   - Aturan bisnis terpusat di `TradingService`.
   - Koneksi DB dipisah di `config/database.php`.
   - Format response JSON dipisahkan ke helper `JsonResponse`.

3. **Error Handling Consistent**
   - Validasi user input melempar `InvalidArgumentException`.
   - Error DB sensitif tidak diekspos ke client.

4. **Transactional Safety**
   - Operasi kritikal (save/delete trade, adjust balance) pakai transaction.

5. **Configuration by Environment**
   - Gunakan variabel `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
   - Contoh tersedia di `.env.example`.

## Menjalankan Proyek

1. Import `trading_journal_db.sql` ke MariaDB/MySQL.
2. (Opsional) set environment variable berdasarkan `.env.example`.
3. Jalankan project di web server lokal (Laragon/XAMPP).
4. Akses halaman utama: `index.php`.

## Catatan Lanjutan (Opsional)

- Tambahkan autentikasi + CSRF token untuk semua aksi API.
- Tambahkan test integration sederhana untuk endpoint utama.
- Pisahkan frontend ke komponen modular (mis. ES module per fitur).
