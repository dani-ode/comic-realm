# ComicRealm 📖🚀

**The ComicRealm** adalah platform *Webcomic Marketplace*, *Continuous Vertical Webtoon Reader*, dan *Creator Economy Publishing Platform* yang dibangun menggunakan arsitektur **Domain-Driven Design (DDD)** berbasis **Laravel 13 Monolith + Inertia.js + Vue 3 + TypeScript + Tailwind CSS v4**, terintegrasi dengan **TriPay Payment Gateway**.

🔗 **Live Demo Application**: [https://comic-realm.daniode.com](https://comic-realm.daniode.com)

---

## 👤 Informasi Pengembang (Developer Contact)

| Keterangan | Detail |
| --- | --- |
| **Nama Lengkap** | La Ode Mahdani |
| **Email** | [hi@daniode.com](mailto:hi@daniode.com) |
| **Nomor HP / WhatsApp** | `085220838947` |
| **Website Demo** | [comic-realm.daniode.com](https://comic-realm.daniode.com) |

---

## 🔑 Akun Demo (Demo Credentials)

Untuk menguji seluruh aliran fitur platform (Admin, Publisher, maupun Reader) di lingkungan lokal atau live demo, gunakan akun seeder berikut:

| Peran (Role) | Email / Username | Password | Fitur & Akses Utama |
| --- | --- | --- | --- |
| **Super Admin** | `admin@comicrealm.test` | `password123` | Control Panel, Monitoring Transaksi TriPay, Moderasi Komik & Approval Payout |
| **Publisher / Creator** | `publisher@comicrealm.test` | `password123` | Publisher Dashboard, Upload Bab Komik WebP, Wallet Ledger & Penarikan Bank |
| **Reader / Pembaca** | `reader@comicrealm.test` | `password123` | Continuous Scroll Reader, Shopping Cart, TriPay Checkout, Bookmark & Komentar |

---

## 🛠️ Stack Teknologi & Arsitektur

- **Backend Framework**: Laravel 13.x (Domain-Driven Design Modular Architecture)
- **Frontend Framework**: Vue 3 (Composition API) + TypeScript + Inertia.js (`@inertiajs/vue3`)
- **Styling & UI**: Tailwind CSS v4 (`@tailwindcss/vite`) + TailAdmin Vue UI Component System
- **Database**: MySQL 8.x (Tipe Data Keuangan `BIGINT`, Rating `DECIMAL`, Payload `JSON`)
- **Payment Gateway Integration**: Abstraksi `PaymentGateway` Contract + Implementasi `TriPayGateway` (`zerosdev/tripay-sdk-php` SDK & HMAC SHA256 Webhook Verification)
- **Testing Engine**: Pest PHP (`pestphp/pest` v3.8) - Unit & Feature Tests
- **Data Transfer Objects**: Spatie Laravel Data (`spatie/laravel-data`)

---

## 🌟 Pilar Utama Platform

```
                          THE ComicRealm Platform
                                     │
       ┌─────────────────────────────┼─────────────────────────────┐
       ▼                             ▼                             ▼
     READ                          SHOP                         PUBLISH
  Free & Paid Chapters         Shopping Cart               Publisher Application
  Continuous Vertical Scroll   TriPay Gateway Integration  Create Comic & Upload Pages
  Lazy Loading Observer        Closed Payment Channels     Ledger Financial Wallet
  Reading Progress & Views     Automatic Entitlements      Withdrawal to Bank Account
       │                             │                             │
       └─────────────────────────────┼─────────────────────────────┘
                                     │
                                     ▼
                               USER ACCOUNT
                                     │
                     ┌───────────────┼───────────────┐
                     ▼               ▼               ▼
                  Bookmark        Rating          Comments
```

1. **Reader Engine (Pembaca Komik Vertical Webtoon)**: Pengalaman membaca komik *continuous vertical scroll* dengan fitur *lazy loading* `IntersectionObserver`, penanda posisi baca otomatis, dan penghitung *view* unik per visitor.
2. **Commerce & Entitlements (Marketplace & Hak Baca)**: Transaksi pembelian bab komik berbayar melalui keranjang belanja (*cart*) yang terintegrasi langsung dengan **TriPay Payment Gateway**. Hak akses membaca diberikan secara otomatis setelah konfirmasi *webhook callback*.
3. **Creator Economy (Publishing & Ledger Keuangan)**: Kreator dapat mendaftar sebagai *Publisher*, mengunggah bab komik berformat WebP secara *batch*, serta menerima komisi bagi hasil yang dicatat dalam *Double Ledger Wallet* (`pending_balance` & `available_balance`) untuk ditarik ke rekening bank.

---

## 📁 Arsitektur Domain-Driven Design (`app/Domain/`)

Logika bisnis diisolasi ke dalam 14 Domain independen di `app/Domain/`:

```
app/
├── Domain/                       # 14 Core Business Domains
│   ├── User/                     # User Registration, Profile, Password, Roles
│   ├── Comic/                    # Comics, Genres, Chapters, Page Upload Engine
│   ├── Publisher/                # Publisher Application, Approval, Profile
│   ├── Reading/                  # Continuous Scroll Reader, Progress, Views
│   ├── Engagement/               # Bookmarks, Rating 1-5, Threaded Comments
│   ├── Cart/                     # Shopping Cart, Item Management
│   ├── Order/                    # Order Creation, Price Snapshot, Order Items
│   ├── Payment/                  # Payment Contracts, Payment Models, Invoice Generation
│   ├── Entitlement/              # Chapter Access Rights & Entitlement Grants
│   ├── Wallet/                   # Publisher Wallet Ledger (Pending/Available Balance)
│   ├── Withdrawal/               # Bank Account Verification & Withdrawal Workflow
│   ├── Point/                    # User Point Wallet & Reward Ledger
│   ├── Notification/             # In-app Notifications
│   └── Admin/                    # Admin Platform Governance
│
├── Infrastructure/               # Third-party Integrations
│   └── Payment/TriPay/           # TriPayGateway, TriPayClient, TriPaySignature
│
├── Http/                         # Thin Controllers per Context (Public, Auth, Admin, etc.)
└── Support/                      # Custom Exceptions, Global Enums & Helpers
```

---

## ⚡ Panduan Memulai (Langkah-Langkah Menjalankan Website di Lingkungan Lokal)

### 1. Prasyarat Sistem (Requirements)
- **PHP**: `>= 8.2` (dengan ekstensi `pdo_mysql`, `mbstring`, `bcmath`, `gd`/`imagick`)
- **Node.js**: `>= 20.x`
- **Composer**: `>= 2.x`
- **Database**: MySQL 8.x

### 2. Pemasangan & Konfigurasi Langkah demi Langkah
```bash
# 1. Clone repositori & masuk ke direktori proyek
git clone https://github.com/dani-ode/comic-realm.git
cd comic-realm

# 2. Install dependency backend (Composer) & frontend (NPM)
composer install
npm install

# 3. Salin file Environment & Hasilkan APP_KEY
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi Database di file .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=comic_realm
DB_USERNAME=root
DB_PASSWORD=

# 5. Konfigurasi Credentials TriPay Gateway Sandbox di .env
TRIPAY_API_KEY="DEV-xxxxxxxxx"
TRIPAY_PRIVATE_KEY="xxxx-xxxx-xxxx-xxxx"
TRIPAY_MERCHANT_CODE="TXXXXX"
TRIPAY_MODE="sandbox"

# 6. Jalankan Migrasi Database & Seeder Data Awal
php artisan migrate --seed

# 7. Hubungkan Storage Link untuk Aset Gambar & Cover Komik
php artisan storage:link

# 8. Build Aset Frontend
npm run build
```

### 3. Menjalankan Server Pengembangan Lokal
Jalankan dua perintah berikut di terminal terpisah:

```bash
# Terminal 1: Server Laravel Backend
php artisan serve

# Terminal 2: Server Vite Frontend Hot Reloading
npm run dev
```

Aplikasi sekarang dapat diakses di peramban web pada alamat: `http://127.0.0.1:8000`.

### 4. Menjalankan Pengujian Otomatis (Pest PHP)
```bash
./vendor/bin/pest
```

---

## 📄 Lisensi

Proyek **The ComicRealm** dikembangkan di bawah lisensi [MIT License](LICENSE).
