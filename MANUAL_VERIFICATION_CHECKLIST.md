# Manual Verification Checklist

Checklist ini dipakai untuk membuktikan skenario pada pengujian iterasi secara langsung di browser dan server, bukan hanya dari baca kode.

## Persiapan Umum

- [ ] Jalankan aplikasi: `php artisan serve`
- [ ] Jalankan queue worker jika dibutuhkan: `php artisan queue:work --verbose`
- [ ] Pastikan scheduler aktif jika pengujian reminder otomatis diperlukan: `php artisan schedule:run`
- [ ] Login sebagai user pasien untuk skenario user, dan login admin untuk skenario admin
- [ ] Buka DevTools browser untuk melihat Network, Console, dan Application
- [ ] Pastikan data uji punya jadwal obat aktif dan notifikasi dapat dipicu

## 1. Akses Saat Offline

### Tujuan
Memastikan halaman utama PWA tetap bisa dibuka saat jaringan mati dan data terakhir masih tampil.

### Browser
- [ ] Buka aplikasi saat online dan pastikan dashboard tampil normal
- [ ] Buka DevTools > Network > aktifkan Offline
- [ ] Reload halaman utama
- [ ] Verifikasi halaman tetap terbuka atau fallback offline tampil
- [ ] Verifikasi data terakhir masih terlihat

### Server
- [ ] Pastikan service worker dan offline scripts termuat tanpa error di Console
- [ ] Cek log browser untuk pesan seperti `[Offline Detector] Initialized`
- [ ] Cek cache storage browser berisi asset utama dan `offline.html`

### Hasil yang harus terlihat
- [ ] Halaman utama tetap bisa diakses saat offline
- [ ] Data terakhir masih tampil
- [ ] Tidak ada error fatal yang memutus halaman

### Status
- [ ] Lulus
- [ ] Perlu diperbaiki

## 2. Penyimpanan Riwayat Konsumsi

### Tujuan
Memastikan status minum obat tersimpan saat user konfirmasi, termasuk saat offline lalu sinkron saat online.

### Browser
- [ ] Buka halaman jadwal atau dashboard user
- [ ] Pilih satu jadwal obat yang masih aktif
- [ ] Klik tombol konfirmasi minum obat saat online
- [ ] Verifikasi status berubah dan riwayat bertambah
- [ ] Ulangi saat offline jika ingin menguji queue lokal
- [ ] Kembali online dan pastikan data tersinkron otomatis

### Server
- [ ] Cek tabel `medication_logs` bertambah satu record untuk jadwal yang dipilih
- [ ] Jika tes offline dilakukan, cek record yang tersimpan punya tanda `offline_synced`
- [ ] Cek log aplikasi untuk pesan sync offline berhasil

### Hasil yang harus terlihat
- [ ] Riwayat konsumsi tersimpan
- [ ] Data offline masuk queue lalu sinkron ketika online
- [ ] Tidak ada duplikasi status untuk satu aksi yang sama

### Status
- [ ] Lulus
- [ ] Perlu diperbaiki

## 3. Notifikasi Pengingat Obat

### Tujuan
Memastikan notifikasi pengingat muncul sesuai jadwal pada browser/device.

### Browser
- [ ] Buka halaman yang memuat OneSignal
- [ ] Pastikan permission notifikasi sudah diizinkan
- [ ] Cek console apakah OneSignal berhasil init tanpa error
- [ ] Trigger jadwal obat yang waktunya sudah dekat atau lewat
- [ ] Verifikasi notifikasi muncul di browser/device

### Server
- [ ] Jalankan `php artisan onesignal:check-config`
- [ ] Pastikan hasil konfigurasi menunjukkan status ready
- [ ] Jika ada queue-based reminder, jalankan worker dan cek job diproses
- [ ] Cek `storage/logs/laravel.log` untuk error pengiriman notifikasi

### Hasil yang harus terlihat
- [ ] Notifikasi muncul tepat waktu
- [ ] User target sesuai email/akun yang login
- [ ] Tidak ada error konfigurasi OneSignal

### Status
- [ ] Lulus
- [ ] Perlu diperbaiki

## 4. Pasien Mengonfirmasi Konsumsi Obat

### Tujuan
Memastikan tombol "sudah minum obat" benar-benar menandai status konsumsi.

### Browser
- [ ] Buka reminder yang tampil di dashboard atau daftar jadwal
- [ ] Klik tombol konfirmasi konsumsi obat
- [ ] Verifikasi item hilang dari reminder aktif atau status berubah
- [ ] Refresh halaman dan pastikan status tetap tersimpan

### Server
- [ ] Cek endpoint konfirmasi menerima request dengan status sukses
- [ ] Cek `medication_logs` tersimpan dengan status `taken`
- [ ] Cek timestamp konsumsi terisi

### Hasil yang harus terlihat
- [ ] Status konsumsi tersimpan di riwayat pasien
- [ ] Reminder yang sama tidak muncul lagi sebagai pending

### Status
- [ ] Lulus
- [ ] Perlu diperbaiki

## 5. Pengingat Kedua

### Tujuan
Memastikan pengingat kedua muncul jika user belum konfirmasi setelah jeda yang ditentukan.

### Browser
- [ ] Buka dashboard user
- [ ] Buat atau pilih jadwal obat yang sudah lewat waktunya
- [ ] Tunggu pengingat pertama muncul
- [ ] Jangan klik konfirmasi
- [ ] Tunggu sampai jeda pengingat kedua terpenuhi
- [ ] Verifikasi pengingat kedua muncul sesuai aturan

### Server
- [ ] Cek log notifikasi pertama sudah tercatat
- [ ] Cek nilai `second_reminder_at` atau mekanisme snooze/reminder lanjutan yang dipakai project
- [ ] Jalankan command reminder bila sistem mengandalkan scheduler/queue
- [ ] Cek `notification_logs` untuk status reminder lanjutan

### Hasil yang harus terlihat
- [ ] Pengingat kedua muncul sesuai aturan
- [ ] Tidak muncul lebih awal dari jeda yang ditetapkan

### Status
- [ ] Lulus
- [ ] Perlu diperbaiki

## Catatan Hasil Uji

- Tanggal uji: __________
- Browser: __________
- User yang dipakai: __________
- Server / environment: __________
- Temuan penting: __________

## Ringkasan Hasil Uji Runtime Saat Ini

- [x] `php artisan onesignal:check-config` = lulus, konfigurasi OneSignal ready
- [x] `php artisan test --filter=Iterasi3Test` = lulus
- [x] `php artisan test --filter=RBACAuthorizationTest` = lulus
- [x] `php artisan test --filter=Iterasi2Test` = lulus
- [x] `php artisan test` penuh = lulus, 69 tests / 149 assertions
- [ ] Verifikasi browser offline end-to-end = belum diuji langsung di browser pada sesi ini
- [ ] Kirim notifikasi end-to-end ke device/browser = belum diuji langsung pada sesi ini
