# Dokumentasi Keamanan Sistem Multi-Gate Authentication

## Ringkasan Eksekutif

Sistem ini mengimplementasikan multi-gate authentication dengan unified login yang aman, dilengkapi dengan berbagai fitur keamanan untuk melindungi dari serangan umum dan memastikan integritas data pengguna.

## Arsitektur Multi-Gate

### Guards yang Tersedia

1. **Web Guard (Admin)**
   - Model: `App\Models\User`
   - Table: `users`
   - Dashboard: `/dashboard`
   - Full access ke semua fitur sistem

2. **Staff Guard**
   - Model: `App\Models\Staff`
   - Table: `staffs`
   - Dashboard: `/staff/profile/edit`
   - Limited access (profile management + specific features)

### Session Isolation

**Trait: `ClearsOtherGuards`**

Memastikan satu user hanya login di satu guard pada satu waktu:

```php
// Logout semua guards kecuali yang ditentukan
$this->clearAllGuardsExcept('web');

// Logout semua guards
$this->clearAllGuards();
```

## Unified Login System

### Priority Logic

1. **Admin First**: Email di-check di `users` table terlebih dahulu
2. **Staff Second**: Jika tidak ada di `users`, check `staffs` table
3. **Staff Validation**: Staff harus memiliki `is_active = true`

### Route

```php
POST /unified-login
Middleware: throttle:login (5 attempts per minute)
```

## Security Features

### 1. Security Headers Middleware

Melindungi dari berbagai serangan:

- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy
- HSTS (Production only)

### 2. Rate Limiting

- **Login:** 5 attempts per minute (per email + IP)
- **2FA:** 5 attempts per minute (per session)
- Messages dalam Bahasa Indonesia

### 3. Password Strength Validation

Requirements:
- Minimal 8 karakter
- Huruf besar + kecil
- Angka
- Optional: Karakter spesial

### 4. Unique Email Across Gates

Mencegah duplicate email di `users` dan `staffs` tables.

### 5. Login Attempt Tracking

Table `login_attempts` untuk audit dan monitoring.

## Test Coverage

**80 tests total:**
- Unified Login: 10 tests
- Password Strength: 8 tests
- Email Uniqueness: 6 tests
- Existing Auth: 56 tests

## Production Recommendations

### Environment

```env
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true
```

### Additional Measures

1. Account lockout after N failed attempts
2. Email notifications for security events
3. Password history check
4. Regular security audits

---

**Version:** 1.0  
**Last Updated:** 2025-11-08
