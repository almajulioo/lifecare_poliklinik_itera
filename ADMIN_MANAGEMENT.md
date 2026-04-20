# 📋 Admin Management Documentation

Dokumentasi lengkap untuk mengelola admin di Poliklinik ITERA.

---

## 🔐 1. FORGOT PASSWORD (Fitur Web)

### Deskripsi
Admin dapat mereset password mereka sendiri melalui halaman web jika lupa password.

### Cara Menggunakan

#### Step 1: Akses Forgot Password Page
```
URL: http://127.0.0.1:8000/admin/forgot-password
atau klik link "Lupa Password?" di halaman login admin
```

#### Step 2: Masukkan Email Admin
- Input email admin yang terdaftar
- Klik tombol "Kirim Link Reset Password"

#### Step 3: Lihat Link Reset (Development Mode)
- Dalam development, sistem akan menampilkan link reset password di layar
- Untuk production, setup email terlebih dahulu (lihat section Email Configuration)
- Klik link untuk membuka halaman reset password

#### Step 4: Input Password Baru
- Masukkan password baru (minimal 8 karakter)
- Konfirmasi password (harus sama)
- Klik "Reset Password"

#### Step 5: Login dengan Password Baru
- Kembali ke halaman login
- Login menggunakan email dan password baru

---

## ⚙️ 2. ARTISAN COMMANDS (Terminal)

### Command 1: Buat Admin Baru

#### Sintaks
```bash
php artisan admin:create {email} {password?}
```

#### Contoh 1: Dengan Password Manual
```bash
php artisan admin:create admin2@itera.ac.id password123
```

Output:
```
Admin baru berhasil dibuat!
Email: admin2@itera.ac.id
Password: password123
Admin ID: 7
```

#### Contoh 2: Password Otomatis (Random)
```bash
php artisan admin:create admin2@itera.ac.id
```

Output:
```
Password baru (otomatis): Kx9mL2qR5vT8wP1s
Admin baru berhasil dibuat!
Email: admin2@itera.ac.id
Password: Kx9mL2qR5vT8wP1s
Admin ID: 7
```

---

### Command 2: Reset Password Admin

#### Sintaks
```bash
php artisan admin:reset-password {email} {password?}
```

#### Contoh 1: Reset Filter Password Manual
```bash
php artisan admin:reset-password admin@itera.ac.id newpassword456
```

Output:
```
Password admin 'admin@itera.ac.id' berhasil direset.
Email: admin@itera.ac.id
Password: newpassword456
```

#### Contoh 2: Generate Password Baru Otomatis
```bash
php artisan admin:reset-password admin@itera.ac.id
```

Output:
```
Password baru (otomatis): Zx7cN3fJ9hK2bM5w
Password admin 'admin@itera.ac.id' berhasil direset.
Email: admin@itera.ac.id
Password: Zx7cN3fJ9hK2bM5w
```

---

## 📧 3. EMAIL CONFIGURATION (Production)

Untuk menggunakan fitur forgot password di production dengan email:

### Step 1: Konfigurasi `.env`
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@itera.ac.id
MAIL_FROM_NAME="Poliklinik ITERA"
```

### Step 2: Test Email
```bash
php artisan tinker
>>> Mail::raw('Test email', function($message) { $message->to('admin@itera.ac.id')->subject('Test'); });
```

---

## 🔍 4. VERIFIKASI DATA

### Cek Semua Admin
```bash
php artisan tinker
>>> App\Models\Admin::all();
```

Output:
```
=> Illuminate\Database\Eloquent\Collection {#4
     all: [
       App\Models\Admin {#5
         id: 1,
         email: "admin@itera.ac.id",
         ...
       },
       App\Models\Admin {#6
         id: 6,
         email: "test@itera.ac.id",
         ...
       },
     ],
   }
```

### Cek Reset Password Tokens
```bash
php artisan tinker
>>> DB::table('admin_password_reset_tokens')->get();
```

---

## 📝 5. QUICK REFERENCE

| Task | Method | Command |
|------|--------|---------|
| Buat Admin Baru | Terminal | `php artisan admin:create email@domain.com password` |
| Buat Admin Auto Password | Terminal | `php artisan admin:create email@domain.com` |
| Reset Password Admin | Terminal | `php artisan admin:reset-password email@domain.com newpass` |
| Reset Auto Password | Terminal | `php artisan admin:reset-password email@domain.com` |
| Admin Lupa Password | Web | Akses `/admin/forgot-password` → Isi email → Reset via link |

---

## ⚠️ 6. TROUBLESHOOTING

### Email tidak terima link?
- Pastikan email sudah dikonfigurasi di `.env`
- Check spam folder
- Untuk development, gunakan link yang ditampilkan di browser

### Error "Email admin tidak ditemukan"?
- Pastikan email sudah terdaftar di tabel `admins`
- Periksa spelling email (case-sensitive)

### Password tidak bisa direset?
- Pastikan token belum kadaluarsa (token berlaku selama session aktif)
- Generate token baru dari forgot password page

---

## 🎯 7. BEST PRACTICES

✅ **DO:**
- Gunakan password yang kuat (minimal 8 karakter)
- Simpan password di tempat aman
- Informasikan password baru ke admin via channel aman
- Regular check list admin untuk security audit

❌ **DON'T:**
- Jangan share password via email atau chat biasa
- Jangan gunakan password yang mudah ditebak
- Jangan simpan password di file biasa di repo

---

Semua setup sudah siap! 🎉
