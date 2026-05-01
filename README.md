# Simple Health Claim Application

Sistem simulasi proses klaim asuransi kesehatan (health insurance) yang mencakup manajemen member, benefit plan, pengajuan klaim, serta perhitungan otomatis limit dan approval.

## Tech Stack

| Layer     | Technology                                                |
| --------- | --------------------------------------------------------- |
| Backend   | PHP 8.4, Laravel 13, Sanctum (Auth)                       |
| Frontend  | Vue 3, Vuetify 3, Pinia, Vite                             |
| Database  | MySQL 8.0                                                 |
| DevOps    | Docker, Docker Compose                                    |
| Others    | Chart.js, GSAP, Three.js, Canvas Confetti                 |

## Prerequisites

Pastikan sudah terinstall di komputer Anda:

- **Docker Desktop** (versi terbaru)
- **Node.js** v20+ & **npm** (untuk menjalankan frontend)

## Project Structure

```
simple-health-claim-application/
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ClaimController.php        ← Core business logic
│   │   │   ├── DashboardController.php
│   │   │   ├── MemberController.php
│   │   │   └── BenefitPlanController.php
│   │   └── Models/
│   │       ├── User.php
│   │       ├── Member.php
│   │       ├── BenefitPlan.php
│   │       └── Claim.php
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   │       └── DatabaseSeeder.php         ← Sample data
│   ├── routes/
│   │   └── api.php
│   ├── Dockerfile
│   ├── docker-compose.yml
│   └── .env.example
│
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   └── AuthNavigation.vue
│   │   ├── pages/
│   │   │   ├── index.vue                  ← Landing page
│   │   │   └── auth/
│   │   │       ├── dashboard.vue
│   │   │       ├── members.vue
│   │   │       ├── benefit-plans.vue
│   │   │       └── claims.vue
│   │   ├── stores/                        ← Pinia state management
│   │   ├── plugins/
│   │   │   └── axios.ts
│   │   └── utils/
│   │       └── format.ts
│   └── package.json
│
└── README.md
```

## Setup & Installation

### 1. Clone Repository

```bash
git clone https://github.com/fhfernandito/simple-health-claim-application.git
cd simple-health-claim-application
```

### 2. Setup Backend (Docker)

```bash
cd backend
```

**a. Jalankan Docker containers:**

```bash
docker compose up -d --build
```

Ini akan menjalankan 3 container:

| Container          | Port   | Keterangan                      |
| ------------------ | ------ | ------------------------------- |
| `laravel_app`      | `8000` | Laravel API server              |
| `laravel_db`       | `3306` | MySQL 8.0 database              |
| `laravel_phpmyadmin` | `8080` | phpMyAdmin (database GUI)     |

**b. Install dependencies & setup database:**

```bash
docker compose exec app composer install

docker compose exec app php artisan key:generate

docker compose exec app php artisan migrate

docker compose exec app php artisan db:seed
```

**c. Verifikasi backend berjalan:**

Buka [http://localhost:8000](http://localhost:8000) di browser.

### 3. Setup Frontend

```bash
cd frontend
```

**a. Install dependencies:**

```bash
npm install
```

**b. Jalankan development server:**

```bash
npm run dev
```

Frontend akan berjalan di [http://localhost:3000](http://localhost:3000) (default Vite).

### 4. Login Credentials

| Field    | Value             |
| -------- | ----------------- |
| Email    | `admin@admin.com` |
| Password | `password`        |

## Data Model

```
┌──────────────────┐       ┌──────────────────┐       ┌──────────────────┐
│     Member       │       │   BenefitPlan    │       │      Claim       │
├──────────────────┤       ├──────────────────┤       ├──────────────────┤
│ id               │       │ id               │       │ id               │
│ name             │──┐    │ plan_name        │       │ member_id (FK)   │
│ policy_number    │  │    │ annual_limit     │       │ claim_date       │
│ benefit_plan_id  │──┼───▶│ created_at       │       │ claim_amount     │
│ created_at       │  │    │ updated_at       │       │ approved_amount  │
│ updated_at       │  │    └──────────────────┘       │ excess_amount    │
└──────────────────┘  │                               │ created_at       │
                      │                               │ updated_at       │
                      └──────────────────────────────▶└──────────────────┘
                         member.id = claim.member_id
```

- `policy_number` di-generate otomatis dengan format `POL-{YEAR}-{SEQUENCE}` (contoh: `POL-2026-00001`)
- `benefit_plan_id` pada Member memiliki foreign key constraint ke BenefitPlan
- `member_id` pada Claim memiliki foreign key constraint ke Member

## Business Logic (Claim Adjudication)

Proses inti dari aplikasi ini berada di `ClaimController@submitClaim`. Ketika user mengajukan klaim, sistem menghitung secara otomatis berapa yang di-approve dan berapa yang menjadi excess.

### Flow:

```
User input claim ──▶ Ambil total claim sebelumnya ──▶ Hitung sisa limit ──▶ Tentukan approved & excess ──▶ Simpan hasil
```

### Rumus:

```
Remaining Limit  = max(Annual Limit − Total Previous Approved YTD, 0)
Approved Amount  = min(Claim Amount, Remaining Limit)
Excess Amount    = Claim Amount − Approved Amount
```

### Contoh Kalkulasi:

Member **Budi Santoso** memiliki **Basic Plan** dengan `Annual Limit = Rp 5.000.000`.

| No | Claim Amount    | Total Prev. Approved | Remaining Limit | Approved        | Excess          | Status   |
| -- | --------------- | -------------------- | --------------- | --------------- | --------------- | -------- |
| 1  | Rp 1.500.000    | Rp 0                 | Rp 5.000.000    | Rp 1.500.000    | Rp 0            | Approved |
| 2  | Rp 2.000.000    | Rp 1.500.000         | Rp 3.500.000    | Rp 2.000.000    | Rp 0            | Approved |
| 3  | Rp 2.500.000    | Rp 3.500.000         | Rp 1.500.000    | Rp 1.500.000    | Rp 1.000.000    | Partial  |
| 4  | Rp 1.000.000    | Rp 5.000.000         | Rp 0            | Rp 0            | Rp 1.000.000    | Rejected |

- **Approved**: Seluruh claim amount di-approve (masih dalam limit)
- **Partial**: Sebagian di-approve, sisanya excess (claim melampaui sisa limit)
- **Rejected**: Seluruh claim menjadi excess (limit sudah habis)

## API Endpoints

Semua endpoint menggunakan prefix `/api` dan memerlukan autentikasi Sanctum (kecuali sign-in).

### Authentication

| Method | Endpoint          | Deskripsi                  |
| ------ | ----------------- | -------------------------- |
| POST   | `/api/sign-in`    | Login, mendapat auth token |
| POST   | `/api/sign-out`   | Logout, revoke token       |
| GET    | `/api/get-user`   | Get current user info      |

### Members

| Method | Endpoint                 | Deskripsi           |
| ------ | ------------------------ | ------------------- |
| GET    | `/api/get-all-members`   | List semua member   |
| POST   | `/api/add-member`        | Tambah member baru  |
| PUT    | `/api/edit-member/{id}`  | Edit data member    |
| DELETE | `/api/delete-member/{id}`| Hapus member        |

### Benefit Plans

| Method | Endpoint                       | Deskripsi              |
| ------ | ------------------------------ | ---------------------- |
| GET    | `/api/get-all-benefit-plans`   | List semua plan        |
| POST   | `/api/add-benefit-plan`        | Tambah plan baru       |
| PUT    | `/api/edit-benefit-plan/{id}`  | Edit plan              |
| DELETE | `/api/delete-benefit-plan/{id}`| Hapus plan             |

### Claims

| Method | Endpoint                           | Deskripsi                           |
| ------ | ---------------------------------- | ----------------------------------- |
| GET    | `/api/get-all-claims`              | List semua claim                    |
| POST   | `/api/submit-claim`                | Submit claim baru (auto-calculate)  |
| DELETE | `/api/delete-claim/{id}`           | Hapus claim                         |
| GET    | `/api/get-member-remaining-limit`  | Cek sisa limit member               |

### Dashboard

| Method | Endpoint                     | Deskripsi                                    |
| ------ | ---------------------------- | -------------------------------------------- |
| GET    | `/api/get-dashboard-stats`   | Statistik, chart data, activity log          |

## Features

### Backend
- CRUD Member dengan auto-generate `policy_number`
- CRUD Benefit Plan dengan proteksi delete (jika ada member yang menggunakan)
- Submit Claim dengan perhitungan otomatis (approved & excess)
- Delete protection pada Member (jika memiliki riwayat klaim)
- Dashboard statistics & aggregation
- Sanctum token-based authentication

### Frontend
- Landing page dengan animasi Three.js particles & GSAP typing effect
- Dashboard dengan Chart.js (bar chart & doughnut chart)
- CRUD interface untuk Members, Benefit Plans, dan Claims
- Real-time remaining limit preview saat submit claim
- Dark/Light mode toggle dengan View Transition API
- Responsive design (desktop & mobile)
- Confetti animation pada successful claim submission

## Database Seeder

Seeder menyediakan data sampel yang sudah siap digunakan:

- **1 Admin user** (`admin@admin.com` / `password`)
- **3 Benefit Plans**: Basic (Rp 5jt), Standard (Rp 10jt), Premium (Rp 25jt)
- **7 Members** dengan distribusi plan yang bervariasi
- **17 Claims** (Januari–April 2026) termasuk kasus approved, partial, dan excess

## Common Docker Commands

```bash
# Jalankan semua containers
docker compose up -d

# Stop semua containers
docker compose down

# Lihat logs
docker compose logs -f app

# Masuk ke container
docker compose exec app bash

# Re-run migration (reset database)
docker compose exec app php artisan migrate:fresh --seed

# Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

## Access Points

| Service          | URL                          |
| ---------------- | ---------------------------- |
| Frontend         | http://localhost:3000         |
| Backend API      | http://localhost:8000/api     |
| phpMyAdmin       | http://localhost:8080         |
