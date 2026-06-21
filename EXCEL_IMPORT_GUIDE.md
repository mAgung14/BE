# Excel Import Template Guide

## Struktur File Excel untuk Import Quiz

### Sheet 1: Quiz Header + Questions

| Judul Kuis | Deskripsi | Kategori | Waktu (menit) | Istirahat (detik) | Akses |
|-----------|-----------|----------|---------------|------------------|-------|
| Ujian Matematika | Ujian semester 1 | Matematika | 60 | 300 | private |
| Berapa 2+2? | 1 | 3 | 4 | 5 | 6 | b |
| Berapa 3+3? | 1 | 5 | 6 | 7 | 8 | b |
| Berapa 5+5? | 2 | 9 | 10 | 11 | 12 | b |

### Penjelasan Kolom

#### Row 1 (Header):
- **Kolom A**: Judul kuis (string, max 255 karakter)
- **Kolom B**: Deskripsi kuis (string, optional)
- **Kolom C**: Kategori kuis (string, max 100 karakter)
- **Kolom D**: Waktu pengerjaan soal (integer, dalam menit, min 1)
- **Kolom E**: Waktu istirahat/break (integer, dalam detik, min 0)
- **Kolom F**: Akses kuis (string, nilai: "publik" atau "private", default: "private")

#### Row 2 ke Row N (Pertanyaan):
- **Kolom A**: Pertanyaan/soal (string, text panjang)
- **Kolom B**: Poin soal (integer, min 1, default: 1)
- **Kolom C**: Jawaban pilihan A (string)
- **Kolom D**: Jawaban pilihan B (string)
- **Kolom E**: Jawaban pilihan C (string)
- **Kolom F**: Jawaban pilihan D (string)
- **Kolom G**: Jawaban yang benar (string, nilai: "a", "b", "c", atau "d", lowercase)

---

## Contoh Lengkap File Excel

### Sheet 1: Kuis Matematika

| Judul Kuis | Deskripsi | Kategori | Waktu (menit) | Istirahat (detik) | Akses |
|-----------|-----------|----------|---------------|------------------|-------|
| Ujian Matematika Dasar | Ujian untuk mengukur pemahaman dasar matematika | Matematika | 60 | 300 | private |

#### Soal-soal:
| Pertanyaan | Poin | Jawaban A | Jawaban B | Jawaban C | Jawaban D | Benar |
|-----------|------|-----------|-----------|-----------|-----------|--------|
| Berapa hasil dari 2 + 2? | 1 | 2 | 4 | 6 | 8 | b |
| Berapa hasil dari 5 × 3? | 1 | 10 | 12 | 15 | 20 | c |
| Berapa hasil dari 10 - 4? | 1 | 4 | 6 | 8 | 14 | b |
| Berapa hasil dari 20 ÷ 4? | 2 | 4 | 5 | 6 | 8 | b |
| Berapa hasil dari 7 × 8? | 2 | 54 | 56 | 64 | 72 | b |
| Berapa hasil dari 100 - 35? | 2 | 55 | 60 | 65 | 75 | c |
| Berapa hasil dari 144 ÷ 12? | 3 | 10 | 11 | 12 | 13 | c |
| Berapa hasil dari 25 + 25? | 3 | 40 | 45 | 50 | 55 | c |
| Berapa hasil dari 99 - 44? | 3 | 45 | 50 | 55 | 60 | c |
| Berapa hasil dari 13 × 9? | 5 | 105 | 115 | 117 | 125 | c |

---

## Tips untuk Membuat File Excel

1. **Format File**: 
   - Gunakan format `.xlsx` (Excel 2007+) untuk kompatibilitas terbaik
   - Alternatif: `.xls` atau `.csv`

2. **Encoding**:
   - Gunakan UTF-8 encoding untuk karakter Indonesia (ä, é, ü, ñ, dll)

3. **Ukuran File**:
   - Max 5MB per file
   - Optimal: < 1MB untuk upload cepat

4. **Jumlah Soal**:
   - Minimal: 1 soal
   - Optimal: 50-100 soal per file
   - Dapat dibuat multiple file dan di-import satu persatu

5. **Validasi Data**:
   - Pastikan kolom jawaban benar hanya berisi: a, b, c, d (lowercase)
   - Pastikan waktu soal >= 1 menit
   - Pastikan akses hanya: publik atau private
   - Jangan kosongkan kolom pertanyaan dan jawaban

6. **Karakter Khusus**:
   - Gunakan simbol matematika: +, -, ×, ÷
   - Gunakan tanda baca standar: ?, !, :, ;, (, )
   - Gunakan newline dalam sel untuk pertanyaan multi-line

---

## Download Template

Template file Excel kosong tersedia di path:
- `resources/templates/kuis-template.xlsx` (jika sudah disediakan)

Atau buat sendiri dengan struktur di atas.

---

## Proses Import

1. Buat file Excel dengan struktur yang sesuai
2. Masuk ke halaman "Kelola Kuis" di frontend
3. Klik tombol "Import dari Excel"
4. Pilih file Excel dari komputer
5. Sistem akan memproses dan membuat kuis baru dengan soal-soal
6. Edit kuis jika diperlukan sebelum publish
7. Klik "Publikasikan" untuk membuat kuis aktif

---

## Troubleshooting

| Error | Penyebab | Solusi |
|-------|---------|--------|
| File Excel tidak terupload | Format file salah | Gunakan .xlsx, .xls, atau .csv |
| Soal tidak masuk | Header tidak sesuai | Pastikan row 1 memiliki semua kolom |
| Jawaban benar tidak terbaca | Format tidak standar | Gunakan lowercase: a, b, c, atau d |
| Total poin 0 | Poin kolom kosong | Isi kolom poin dengan angka positif |
| Kuis tidak bisa publish | Tidak ada soal | Pastikan minimal 1 soal dalam kuis |

---

## Contoh Curl Upload Excel

```bash
curl -X POST http://192.168.1.8:8000/api/kuis/import-excel \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "file=@/path/to/kuis.xlsx"
```

Response:
```json
{
  "message": "Kuis berhasil diimpor dari Excel.",
  "data": {
    "kuis_id": 2,
    "judul": "Ujian Matematika Dasar",
    "jumlah_soal": 10,
    "kode_kuis": "ABC456"
  }
}
```
