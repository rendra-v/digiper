# Backend API Schema & Database Documentation
## Complete Reference for APIs, Endpoints, & Database Design

**Versi:** 1.0  
**Target Audience:** Backend Developer / API Developer / Database Admin  
**Purpose:** Technical reference for all APIs and database schemas  

---

## 📋 Daftar Isi

1. [API Endpoints](#api-endpoints)
2. [Request/Response Formats](#requestresponse-formats)
3. [Database Schema](#database-schema)
4. [SQL Queries & Reports](#sql-queries--reports)
5. [Error Codes & Handling](#error-codes--handling)
6. [Rate Limiting & Throttling](#rate-limiting--throttling)
7. [Authentication & Authorization](#authentication--authorization)

---

## 🔌 API Endpoints

### Base URL
```
Development:  http://digiper.test
Production:   https://api.mahkamahagung.go.id/digiper
```

### API Version
```
v1 (current)
```

---

## 📋 Endpoints Reference

### 1. PERKARAS (Case Management)

#### GET /perkaras
**List all perkaras with pagination and filters**

```
Method: GET
URL: /perkaras?page=1&per_page=10&kamar=TUN&jenis=1&search=225/PK

Query Parameters:
- page (int, optional): Page number, default 1
- per_page (int, optional): Items per page, default 10
- kamar (string, optional): Filter by kamar (TUN, Perdata, etc)
- jenis (int, optional): Filter by jenis_perkara_id
- search (string, optional): Search in no_registrasi & amar
- sort (string, optional): Sort by column (e.g., -tanggal_masuk)

Response: 200 OK
{
    "data": [
        {
            "id": 1,
            "no_registrasi": "225/PK/TUN/2025",
            "tanggal_masuk": "2024-10-31",
            "tanggal_putus": "2025-12-10",
            "kamar": "TUN",
            "jenis_perkara": {
                "id": 2,
                "nama": "PK"
            },
            "usia_hari": 405,
            "status_biaya": "kena",
            "biaya": 2000000,
            "created_at": "2026-05-21T10:00:00Z"
        }
    ],
    "links": {
        "first": "http://digiper.test/perkaras?page=1",
        "last": "http://digiper.test/perkaras?page=3",
        "prev": null,
        "next": "http://digiper.test/perkaras?page=2"
    },
    "meta": {
        "current_page": 1,
        "total": 125,
        "per_page": 10,
        "last_page": 13
    }
}
```

---

#### POST /perkaras
**Create new perkara (typically via import)**

```
Method: POST
URL: /perkaras

Headers:
- Content-Type: application/json
- Accept: application/json

Body:
{
    "no_registrasi": "225/PK/TUN/2025",
    "tanggal_masuk": "2024-10-31",
    "tanggal_putus": "2025-12-10",
    "kamar": "TUN",
    "jenis_perkara_id": 2,
    "nama_p1": "Dr. H. Bambang Sutrisno",
    "nama_p2": "Dr. Siti Nurhaliza",
    "hakim_p1_id": 5,
    "hakim_p2_id": 6,
    "amar": "Perkara ditolak",
    "biaya": 2000000
}

Response: 201 Created
{
    "data": {
        "id": 126,
        "no_registrasi": "225/PK/TUN/2025",
        "status": "kena",
        "message": "Perkara created successfully"
    }
}

Errors:
400 Bad Request - Validation failed
409 Conflict - no_registrasi already exists
```

---

#### GET /perkaras/{id}
**Get detailed perkara information**

```
Method: GET
URL: /perkaras/1

Response: 200 OK
{
    "data": {
        "id": 1,
        "no_registrasi": "225/PK/TUN/2025",
        "tanggal_masuk": "2024-10-31",
        "tanggal_putus": "2025-12-10",
        "kamar": "TUN",
        "jenis_perkara": {
            "id": 2,
            "nama": "PK"
        },
        "usia_hari": 405,
        "status_biaya": "kena",
        "biaya": 2000000,
        "amar": "Perkara ditolak",
        "detail_biaya": [
            {
                "id": 10,
                "komponen": "Materai",
                "nominal": 10000,
                "persentase": null
            },
            {
                "id": 11,
                "komponen": "ATK",
                "nominal": 99000,
                "persentase": 5
            }
        ],
        "distribusi_honor": [
            {
                "id": 20,
                "role": "ketua_majelis",
                "hakim_nama": "Dr. H. Bambang",
                "nominal": 700000,
                "persentase": 35
            }
        ]
    }
}
```

---

#### PUT /perkaras/{id}
**Update perkara**

```
Method: PUT
URL: /perkaras/1

Body:
{
    "amar": "Perkara dikabulkan sebagian",
    "biaya": 2500000
}

Response: 200 OK
{
    "data": {
        "id": 1,
        "no_registrasi": "225/PK/TUN/2025",
        "biaya": 2500000,
        "updated_at": "2026-05-21T15:30:00Z"
    }
}
```

---

#### DELETE /perkaras/{id}
**Delete perkara (soft delete)**

```
Method: DELETE
URL: /perkaras/1

Response: 204 No Content

or 200 OK with:
{
    "message": "Perkara deleted"
}
```

---

### 2. IMPORT ENDPOINT

#### POST /perkaras (with file upload)
**Import perkaras from Excel file**

```
Method: POST
URL: /perkaras

Headers:
- Content-Type: multipart/form-data
- Accept: application/json

Body (form-data):
- file: [Excel file]

Response: 200 OK
{
    "success": true,
    "imported_count": 125,
    "failed_count": 2,
    "errors": [
        {
            "row": 15,
            "no_registrasi": "228/PK/2025",
            "error": "Invalid date format"
        }
    ],
    "message": "125 records imported successfully"
}

Errors:
400 Bad Request - File validation failed
413 Payload Too Large - File > 25MB
422 Unprocessable Entity - Import logic error
```

---

### 3. HAKIMS (Judge Management)

#### GET /hakims
**List all judges**

```
Method: GET
URL: /hakims?active=1&sort=-urutan_seniority

Response: 200 OK
{
    "data": [
        {
            "id": 1,
            "nama": "Dr. H. Bambang Sutrisno",
            "nip": "19550315 198003 1 001",
            "pangkat": "Ketua",
            "urutan_seniority": 1,
            "status_aktif": true,
            "total_perkara": 45,
            "total_honor": 45000000
        }
    ]
}
```

---

#### POST /hakims
**Create new judge**

```
Method: POST
URL: /hakims

Body:
{
    "nama": "Dr. Budi Santoso",
    "nip": "19600420 198703 1 002",
    "pangkat": "Anggota",
    "urutan_seniority": 10,
    "status_aktif": true,
    "tanggal_mulai": "2020-01-01"
}

Response: 201 Created
{
    "data": {
        "id": 15,
        "nama": "Dr. Budi Santoso",
        "message": "Judge created successfully"
    }
}
```

---

#### GET /hakims/{id}/honor
**Get honor summary for specific judge**

```
Method: GET
URL: /hakims/1/honor

Response: 200 OK
{
    "data": {
        "id": 1,
        "nama": "Dr. H. Bambang Sutrisno",
        "total_honor": 45000000,
        "perkara_count": 45,
        "breakdown": {
            "as_p1": 20000000,
            "as_p2": 15000000,
            "as_p3": 10000000
        },
        "by_jenis": {
            "PK": 25000000,
            "TUN": 20000000
        }
    }
}
```

---

### 4. LAPORAN (Reports)

#### GET /laporan
**Get report generation form**

```
Method: GET
URL: /laporan

Response: 200 OK (HTML form)
```

---

#### POST /laporan/cetak
**Generate report for period**

```
Method: POST
URL: /laporan/cetak

Body:
{
    "tanggal_mulai": "2025-01-01",
    "tanggal_selesai": "2025-12-31",
    "jenis_perkara_id": 2,
    "format": "pdf"  // or "excel"
}

Response: 200 OK (file download)
Content-Disposition: attachment; filename="laporan_2025.pdf"
```

---

#### GET /laporan/export/{format}
**Export data in specified format**

```
Method: GET
URL: /laporan/export/excel?filter=jenis&value=2

Formats: pdf, excel, csv

Response: 200 OK (file download)
```

---

### 5. DASHBOARD

#### GET /api/stats
**Get dashboard statistics**

```
Method: GET
URL: /api/stats

Response: 200 OK
{
    "data": {
        "total_perkara": 125,
        "perkara_valid": 118,
        "total_biaya": 236000000,
        "total_hakim": 12,
        "by_jenis": {
            "PK": {
                "count": 50,
                "biaya": 100000000
            },
            "TUN": {
                "count": 75,
                "biaya": 136000000
            }
        }
    }
}
```

---

#### GET /api/recap/total
**Get total recap (auto-sync)**

```
Method: GET
URL: /api/recap/total

Response: 200 OK
{
    "data": {
        "rekap_komponen": { ... },
        "rekap_honor": { ... },
        "rekap_hakim": { ... },
        "grand_total": 236000000,
        "last_sync": "2026-05-21T16:45:00Z"
    }
}
```

---

## 📦 Request/Response Formats

### Standard Request Format

```json
{
    "field_name": "value",
    "nested": {
        "sub_field": "value"
    }
}
```

### Standard Response Format

**Success (200/201):**
```json
{
    "success": true,
    "data": { ... },
    "message": "Operation successful"
}
```

**Error (4xx/5xx):**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "details": {
            "field_name": ["Error message 1", "Error message 2"]
        }
    }
}
```

### Pagination Format

```json
{
    "data": [ ... ],
    "links": {
        "first": "url",
        "last": "url",
        "prev": "url",
        "next": "url"
    },
    "meta": {
        "current_page": 1,
        "total": 125,
        "per_page": 10,
        "last_page": 13,
        "from": 1,
        "to": 10
    }
}
```

---

## 🗄️ Database Schema

### Complete Schema Diagram

```
perkaras (Cases)
├── id (PK)
├── no_registrasi (unique)
├── tanggal_masuk (date)
├── tanggal_putus (date)
├── kamar (varchar)
├── jenis_perkara_id (FK)
├── nama_p1 to p5 (varchar)
├── hakim_p1_id to p3_id (FK)
├── hakim_pemilah_id (FK, nullable)
├── tim_kepaniteraan_id (FK, nullable)
├── amar (text)
├── biaya (decimal)
├── status_biaya (enum)
└── timestamps & soft_delete

hakims (Judges)
├── id (PK)
├── nama (varchar)
├── nip (unique)
├── pangkat (enum)
├── urutan_seniority (int)
├── status_aktif (boolean)
├── tanggal_mulai (date)
├── tanggal_selesai (date, nullable)
└── timestamps & soft_delete

detail_biaya_perkaras (Cost Breakdown)
├── id (PK)
├── perkara_id (FK)
├── komponen_biaya_id (FK)
├── nominal (decimal)
├── persentase (decimal, nullable)
└── timestamps

distribusi_honors (Honor Distribution)
├── id (PK)
├── perkara_id (FK)
├── hakim_id (FK, nullable)
├── role (enum)
├── nominal (decimal)
├── persentase (decimal)
├── keterangan (varchar)
└── timestamps

jenis_perkaras (Case Types)
├── id (PK)
├── nama (varchar)
├── kode (varchar)
└── timestamps

komponen_biayas (Cost Components)
├── id (PK)
├── nama (varchar)
├── tipe (enum: fixed/percent)
├── nilai (decimal)
├── urutan (int)
└── timestamps

tarif_biayas (Tariff Master)
├── id (PK)
├── jenis_perkara_id (FK)
├── tarif_normal (decimal)
├── tarif_elektronik (decimal, nullable)
├── berlaku_mulai (date)
├── berlaku_sampai (date, nullable)
└── timestamps

tim_kepaniteraanss (Teams)
├── id (PK)
├── kode (varchar, unique)
├── nama (varchar)
├── deskripsi (text)
└── timestamps

pejabats (Officials)
├── id (PK)
├── nama (varchar)
├── nip (unique)
├── jabatan (varchar)
├── tanggal_mulai (date)
├── tanggal_selesai (date, nullable)
├── status_aktif (boolean)
└── timestamps & soft_delete
```

---

## 📊 SQL Queries & Reports

### Query 1: Total Biaya Per Jenis Perkara

```sql
SELECT 
    jp.nama as 'Jenis Perkara',
    COUNT(p.id) as 'Volume',
    SUM(p.biaya) as 'Total Biaya',
    AVG(p.biaya) as 'Rata-rata'
FROM perkaras p
JOIN jenis_perkaras jp ON p.jenis_perkara_id = jp.id
WHERE p.status_biaya = 'kena'
GROUP BY p.jenis_perkara_id
ORDER BY SUM(p.biaya) DESC;
```

---

### Query 2: Honor Per Hakim

```sql
SELECT 
    h.nama as 'Nama Hakim',
    h.pangkat,
    COUNT(DISTINCT dh.perkara_id) as 'Jumlah Perkara',
    SUM(dh.nominal) as 'Total Honor'
FROM hakims h
LEFT JOIN distribusi_honors dh ON h.id = dh.hakim_id
WHERE h.status_aktif = true
GROUP BY h.id
ORDER BY h.urutan_seniority;
```

---

### Query 3: Detail Breakdown Biaya Per Perkara

```sql
SELECT 
    p.no_registrasi,
    kb.nama as 'Komponen',
    dbp.nominal as 'Nominal',
    ROUND((dbp.nominal / p.biaya * 100), 2) as 'Persentase (%)'
FROM detail_biaya_perkaras dbp
JOIN perkaras p ON dbp.perkara_id = p.id
JOIN komponen_biayas kb ON dbp.komponen_biaya_id = kb.id
WHERE p.id = ?
ORDER BY kb.urutan;
```

---

### Query 4: Perkara Report (Monthly)

```sql
SELECT 
    YEAR(p.tanggal_putus) as 'Tahun',
    MONTH(p.tanggal_putus) as 'Bulan',
    jp.nama as 'Jenis Perkara',
    COUNT(p.id) as 'Volume',
    SUM(p.biaya) as 'Total Biaya'
FROM perkaras p
JOIN jenis_perkaras jp ON p.jenis_perkara_id = jp.id
WHERE p.status_biaya = 'kena'
    AND YEAR(p.tanggal_putus) = 2025
GROUP BY YEAR(p.tanggal_putus), MONTH(p.tanggal_putus), jp.id
ORDER BY MONTH(p.tanggal_putus);
```

---

### Query 5: Complex: Hakim Distribution Report

```sql
SELECT 
    h.nama,
    SUM(CASE WHEN dh.role = 'ketua_majelis' THEN dh.nominal ELSE 0 END) as 'Ketua Majelis',
    SUM(CASE WHEN dh.role = 'hakim_anggota' THEN dh.nominal ELSE 0 END) as 'Hakim Anggota',
    SUM(dh.nominal) as 'Total Honor',
    COUNT(DISTINCT dh.perkara_id) as 'Jumlah Perkara'
FROM hakims h
LEFT JOIN distribusi_honors dh ON h.id = dh.hakim_id
WHERE h.status_aktif = true
GROUP BY h.id
ORDER BY SUM(dh.nominal) DESC;
```

---

## ⚠️ Error Codes & Handling

### HTTP Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 204 | No Content | Delete successful |
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Duplicate entry |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Custom Error Codes

```
VALIDATION_ERROR          - Input validation failed
IMPORT_ERROR              - Excel import failed
NOT_FOUND                 - Resource not found
UNAUTHORIZED              - Authentication required
FORBIDDEN                 - Access denied
DUPLICATE_ENTRY           - Unique constraint violation
CALCULATION_ERROR         - Business logic error
SYSTEM_ERROR              - Unexpected error
```

### Error Response Example

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Data validation failed",
        "details": {
            "no_registrasi": [
                "The no_registrasi field is required."
            ],
            "tanggal_masuk": [
                "The tanggal_masuk field must be a valid date."
            ]
        }
    },
    "timestamp": "2026-05-21T16:45:00Z"
}
```

---

## 🚦 Rate Limiting & Throttling

### Rate Limits

```
Authenticated:    300 requests/minute per user
Anonymous:        60 requests/minute per IP
Import endpoint:  5 requests/minute
Report endpoint:  10 requests/minute
```

### Headers

**Response Headers:**
```
X-RateLimit-Limit:       300
X-RateLimit-Remaining:   298
X-RateLimit-Reset:       1653128400
X-RateLimit-Retry-After: 60
```

**When Limit Exceeded (429):**
```json
{
    "message": "Rate limit exceeded",
    "retry_after": 60
}
```

---

## 🔐 Authentication & Authorization

### Authentication Methods

#### 1. Session-Based (for web)
```
Laravel session cookies
POST /login
```

#### 2. Token-Based (for API)
```
Authorization: Bearer {token}

Token obtained from:
POST /api/auth/login
{
    "email": "admin@mahkamah.go.id",
    "password": "password"
}

Response:
{
    "access_token": "eyJ0eXAiOiJKV1Q...",
    "token_type": "Bearer",
    "expires_in": 3600
}
```

### Authorization Levels

```
SUPER_ADMIN       - Full system access
ADMIN             - Manage data, generate reports
OPERATOR          - View-only, can't modify
VIEWER            - Read-only dashboard
```

### Middleware

```php
// Authenticated users only
Route::middleware('auth')->group(function () {
    Route::resource('perkaras', PerkaraController::class);
});

// API token authentication
Route::middleware('auth:api')->group(function () {
    Route::get('/api/stats', ...);
});

// Role-based
Route::middleware('role:admin')->group(function () {
    Route::delete('/perkaras/{id}', ...);
});
```

---

## 📐 Data Validation Rules

### Perkara Validation

```php
[
    'no_registrasi' => 'required|string|unique:perkaras',
    'tanggal_masuk' => 'required|date',
    'tanggal_putus' => 'required|date|after_or_equal:tanggal_masuk',
    'kamar' => 'required|string',
    'jenis_perkara_id' => 'required|exists:jenis_perkaras,id',
    'nama_p1' => 'nullable|string|max:255',
    'hakim_p1_id' => 'nullable|exists:hakims,id',
    'amar' => 'nullable|string',
    'biaya' => 'required|numeric|min:0',
]
```

### Import File Validation

```php
[
    'file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb|max:25600',
]

// Column requirements in Excel:
// - no_registrasi (required)
// - tanggal_masuk (required, YYYY-MM-DD format)
// - tanggal_putus (required, YYYY-MM-DD format)
// - kamar (required)
// - jenis_perkara or kamar to determine type
// - nama_p1, nama_p2, nama_p3 (optional)
// - amar (optional)
```

---

## 📊 Database Backup & Recovery

### Backup Procedure

```bash
# Manual backup
mysqldump -u root digiper > digiper_backup_$(date +%Y%m%d_%H%M%S).sql

# Scheduled backup (cron)
0 2 * * * mysqldump -u root digiper > /backups/digiper_$(date +\%Y\%m\%d).sql
```

### Recovery

```bash
# Restore from backup
mysql -u root digiper < digiper_backup_20260521_020000.sql

# Verify
mysql -u root -e "SELECT COUNT(*) FROM digiper.perkaras;"
```

---

## 🔗 Related Documentation

- See [BACKEND_DEVELOPMENT_GUIDE.md](BACKEND_DEVELOPMENT_GUIDE.md) for implementation details
- See [DOKUMENTASI_SISTEM.md](DOKUMENTASI_SISTEM.md) for system architecture
- See [FRONTEND_DEVELOPMENT_GUIDE.md](FRONTEND_DEVELOPMENT_GUIDE.md) for frontend integration

---

## 📝 API Testing Examples

### Using cURL

```bash
# List perkaras
curl -X GET "http://digiper.test/perkaras?page=1" \
  -H "Accept: application/json"

# Create perkara (requires auth)
curl -X POST "http://digiper.test/perkaras" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "no_registrasi": "225/PK/2025",
    "tanggal_masuk": "2024-10-31",
    "tanggal_putus": "2025-12-10",
    "biaya": 2000000
  }'
```

### Using Postman

1. Import collection from: `storage/postman/digiper.postman_collection.json`
2. Set environment variables:
   - `base_url`: http://digiper.test
   - `auth_token`: (obtain via login)
3. Run requests from collection

---

**Last Updated:** Mei 2026  
**Status:** Production Ready
