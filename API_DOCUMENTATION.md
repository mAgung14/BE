# API Documentation - Kelola Kuis System

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint yang dilindungi memerlukan JWT token dalam header:
```
Authorization: Bearer <token>
```

---

## 1. AUTHENTICATION ENDPOINTS

### Register
**POST** `/register`
- No authentication required
- Request body:
```json
{
  "name": "Guru Name",
  "email": "guru@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
- Response (201):
```json
{
  "message": "Registrasi berhasil.",
  "data": {
    "user": {...},
    "token": "eyJ0eXAi...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Login
**POST** `/login`
- No authentication required
- Request body:
```json
{
  "email": "guru@example.com",
  "password": "password123"
}
```
- Response (200):
```json
{
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 5,
      "name": "Guru Test",
      "email": "guru2@example.com"
    },
    "token": "eyJ0eXAi...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Get Current User
**GET** `/me`
- Requires: JWT Token
- Response (200):
```json
{
  "data": {
    "id": 5,
    "name": "Guru Test",
    "email": "guru2@example.com"
  }
}
```

### Logout
**POST** `/logout`
- Requires: JWT Token
- Response (200):
```json
{
  "message": "Logout berhasil."
}
```

---

## 2. QUIZ MANAGEMENT ENDPOINTS

### List Quiz (Kelola Kuis)
**GET** `/kuis`
- Requires: JWT Token
- Query parameters:
  - `status` (optional): `semua`, `draft`, `aktif`, `selesai` (default: `semua`)
  - `search` (optional): Cari berdasarkan judul atau deskripsi
  - `per_page` (optional): Jumlah item per halaman (default: 10)

- Example: `/kuis?status=aktif&search=Matematika&per_page=15`

- Response (200):
```json
{
  "message": "Data kuis berhasil diambil.",
  "data": [
    {
      "kuis_id": 1,
      "judul": "Matematika Dasar",
      "deskripsi": "Kuis matematika untuk kelas 1",
      "kategori": "Matematika",
      "status": "aktif",
      "akses": "publik",
      "kode_kuis": "ABC123",
      "soal_waktu": 60,
      "jumlah_soal": 10,
      "total_poin": 100,
      "tgl_dibuat": "2026-06-08",
      "is_published": true
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

### Create Quiz (Buat Kuis Baru)
**POST** `/kuis`
- Requires: JWT Token
- Request body:
```json
{
  "judul": "Ujian Matematika",
  "deskripsi": "Ujian semester untuk kelas 10",
  "kategori": "Matematika",
  "soal_waktu": 120,
  "perm_istirahat": 300,
  "akses": "private"
}
```
- Response (201):
```json
{
  "message": "Kuis berhasil dibuat.",
  "data": {
    "kuis_id": 1,
    "judul": "Ujian Matematika",
    "kode_kuis": "XYZ789",
    "akses": "private",
    "status": "draft"
  }
}
```

### Get Quiz Detail
**GET** `/kuis/{id}`
- Requires: JWT Token
- Response (200):
```json
{
  "message": "Detail kuis berhasil diambil.",
  "data": {
    "kuis_id": 1,
    "judul": "Ujian Matematika",
    "deskripsi": "Ujian semester",
    "kategori": "Matematika",
    "status": "draft",
    "akses": "private",
    "kode_kuis": "XYZ789",
    "soal_waktu": 120,
    "perm_istirahat": 300,
    "is_published": false,
    "jumlah_soal": 0,
    "total_poin": 0,
    "guru": {
      "id": 5,
      "name": "Guru Test",
      "email": "guru@example.com"
    },
    "soal": [],
    "tgl_dibuat": "2026-06-08",
    "created_at": "2026-06-08T10:00:00Z",
    "updated_at": "2026-06-08T10:00:00Z"
  }
}
```

### Update Quiz
**PUT** `/kuis/{id}`
- Requires: JWT Token
- Request body (semua field optional):
```json
{
  "judul": "Ujian Matematika Lanjut",
  "deskripsi": "Ujian semester untuk kelas 11",
  "kategori": "Matematika",
  "soal_waktu": 150,
  "perm_istirahat": 600,
  "akses": "publik",
  "status": "aktif",
  "is_published": true
}
```
- Response (200):
```json
{
  "message": "Kuis berhasil diperbarui.",
  "data": {
    "kuis_id": 1,
    "judul": "Ujian Matematika Lanjut",
    "status": "aktif",
    "akses": "publik"
  }
}
```

### Publish Quiz
**POST** `/kuis/{id}/publish`
- Requires: JWT Token
- Notes: Kuis harus memiliki minimal 1 soal untuk dipublikasikan
- Response (200):
```json
{
  "message": "Kuis berhasil dipublikasikan.",
  "data": {
    "kuis_id": 1,
    "is_published": true,
    "status": "aktif"
  }
}
```

### Delete Quiz
**DELETE** `/kuis/{id}`
- Requires: JWT Token
- Response (200):
```json
{
  "message": "Kuis 'Ujian Matematika' berhasil dihapus."
}
```

### Dashboard Summary
**GET** `/kuis/summary`
- Requires: JWT Token
- Response (200):
```json
{
  "data": {
    "total_kuis": 5,
    "kuis_aktif": 3,
    "kuis_selesai": 1,
    "latest": [
      {
        "kuis_id": 5,
        "judul": "Kuis Terbaru",
        "kategori": "IPA",
        "status": "aktif",
        "tgl_dibuat": "2026-06-08"
      }
    ]
  }
}
```

### Import Quiz from Excel
**POST** `/kuis/import-excel`
- Requires: JWT Token
- Content-Type: `multipart/form-data`
- Request:
  - `file` (required): Excel file (.xlsx, .xls, .csv), max 5MB

- Excel Format:
  - Row 1 (Header): `Judul | Deskripsi | Kategori | Waktu (menit) | Istirahat (detik) | Akses (publik/private)`
  - Row 2+ (Questions):
    - Column A: Pertanyaan
    - Column B: Poin
    - Column C: Jawaban A
    - Column D: Jawaban B
    - Column E: Jawaban C
    - Column F: Jawaban D
    - Column G: Jawaban Benar (a/b/c/d)

- Response (201):
```json
{
  "message": "Kuis berhasil diimpor dari Excel.",
  "data": {
    "kuis_id": 2,
    "judul": "Kuis Impor",
    "jumlah_soal": 10,
    "kode_kuis": "ABC456"
  }
}
```

---

## 3. QUESTION (SOAL) MANAGEMENT ENDPOINTS

### Get All Questions in Quiz
**GET** `/kuis/{kuisId}/soal`
- Requires: JWT Token
- Response (200):
```json
{
  "message": "Data soal berhasil diambil.",
  "data": [
    {
      "id": 1,
      "urutan": 1,
      "soal_soal": "Berapa hasil dari 2+2?",
      "gambar_soal": null,
      "tipe_soal": "pilihan_ganda",
      "poin": 10,
      "jawaban_a": "3",
      "jawaban_b": "4",
      "jawaban_c": "5",
      "jawaban_d": "6",
      "gambar_jawaban_a": null,
      "gambar_jawaban_b": null,
      "gambar_jawaban_c": null,
      "gambar_jawaban_d": null,
      "jawaban_benar": "b",
      "created_at": "2026-06-08T10:00:00Z",
      "updated_at": "2026-06-08T10:00:00Z"
    }
  ]
}
```

### Create Question
**POST** `/soal`
- Requires: JWT Token
- Content-Type: `multipart/form-data` (jika ada gambar) atau `application/json`
- Request body:
```json
{
  "kuis_id": 1,
  "soal_soal": "Berapa hasil dari 3+3?",
  "tipe_soal": "pilihan_ganda",
  "poin": 10,
  "jawaban_a": "5",
  "jawaban_b": "6",
  "jawaban_c": "7",
  "jawaban_d": "8",
  "jawaban_benar": "b"
}
```

- Dengan gambar (form-data):
  - `kuis_id` (integer)
  - `soal_soal` (string)
  - `tipe_soal` (string)
  - `poin` (integer)
  - `jawaban_a` (string)
  - `jawaban_b` (string)
  - `jawaban_c` (string)
  - `jawaban_d` (string)
  - `jawaban_benar` (string: a/b/c/d)
  - `gambar_soal` (file: image)
  - `gambar_jawaban_a` (file: image, optional)
  - `gambar_jawaban_b` (file: image, optional)
  - `gambar_jawaban_c` (file: image, optional)
  - `gambar_jawaban_d` (file: image, optional)

- Response (201):
```json
{
  "message": "Soal berhasil ditambahkan.",
  "data": {
    "id": 2,
    "kuis_id": 1,
    "soal_soal": "Berapa hasil dari 3+3?",
    "urutan": 2,
    "poin": 10,
    "jawaban_benar": "b"
  }
}
```

### Get Question Detail
**GET** `/soal/{id}`
- Requires: JWT Token
- Response (200):
```json
{
  "message": "Detail soal berhasil diambil.",
  "data": {
    "id": 1,
    "kuis_id": 1,
    "soal_soal": "Berapa hasil dari 2+2?",
    "gambar_soal": null,
    "tipe_soal": "pilihan_ganda",
    "poin": 10,
    "urutan": 1,
    "jawaban_a": "3",
    "jawaban_b": "4",
    "jawaban_c": "5",
    "jawaban_d": "6",
    "jawaban_benar": "b"
  }
}
```

### Update Question
**PUT** `/soal/{id}`
- Requires: JWT Token
- Request body (semua field optional):
```json
{
  "soal_soal": "Berapa hasil dari 2+2? (Diperbaharui)",
  "poin": 15,
  "jawaban_a": "3",
  "jawaban_b": "4",
  "jawaban_c": "5",
  "jawaban_d": "6",
  "jawaban_benar": "b"
}
```
- Response (200):
```json
{
  "message": "Soal berhasil diperbarui.",
  "data": {
    "id": 1,
    "soal_soal": "Berapa hasil dari 2+2? (Diperbaharui)",
    "poin": 15
  }
}
```

### Delete Question
**DELETE** `/soal/{id}`
- Requires: JWT Token
- Response (200):
```json
{
  "message": "Soal berhasil dihapus."
}
```

### Reorder Questions
**POST** `/kuis/{kuisId}/soal/reorder`
- Requires: JWT Token
- Request body:
```json
{
  "soal_ids": [3, 1, 2, 5, 4]
}
```
- Response (200):
```json
{
  "message": "Urutan soal berhasil diperbarui."
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthorized"
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized. Anda tidak memiliki akses ke kuis ini."
}
```

### 422 Unprocessable Entity
```json
{
  "message": "Validasi gagal.",
  "errors": {
    "judul": ["Judul kuis wajib diisi."],
    "kategori": ["Kategori kuis wajib diisi."]
  }
}
```

### 404 Not Found
```json
{
  "message": "Resource tidak ditemukan."
}
```

---

## Status Codes
- `200` - Success (GET, PUT, DELETE)
- `201` - Created (POST)
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity
- `500` - Internal Server Error

---

## Notes for Frontend Implementation

1. **Token Management**:
   - Simpan token di localStorage atau sessionStorage setelah login
   - Attach token ke setiap request dalam header `Authorization: Bearer <token>`
   - Implement auto-logout ketika token expired (401 response)

2. **Image Upload**:
   - Gunakan `multipart/form-data` untuk upload gambar
   - Max file size: 2MB per gambar
   - Supported formats: JPEG, PNG, JPG, GIF

3. **Excel Import Format**:
   - Buat template Excel dengan struktur sesuai dokumentasi
   - Max file size: 5MB
   - Supported formats: .xlsx, .xls, .csv

4. **Access Control**:
   - `publik`: Quiz dapat diakses oleh semua user dengan kode kuis
   - `private`: Quiz hanya dapat diakses oleh guru yang membuatnya

5. **Quiz Status**:
   - `draft`: Sedang dikerjakan, belum dipublikasikan
   - `aktif`: Sudah dipublikasikan dan sedang berlangsung
   - `selesai`: Quiz sudah berakhir
