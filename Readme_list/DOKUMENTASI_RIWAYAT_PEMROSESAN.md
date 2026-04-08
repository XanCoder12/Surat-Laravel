# 📋 Dokumentasi Fitur: Riwayat Pemrosesan Surat

## 📌 Ringkasan
Fitur baru di dashboard admin yang menampilkan **siapa saja (admin/pengolah) yang telah memproses setiap surat** dalam bulan berjalan dengan detail nama admin per tahapan.

## 🎯 Tujuan
Memberikan transparansi tentang siapa saja di tim admin yang terlibat dalam setiap proses surat, sehingga:
- Memudahkan tracking akuntabilitas
- Mengetahui siapa pengolah surat di setiap tahapan
- Audit trail yang jelas untuk keperluan laporan

## 📊 Tampilan Fitur

### Lokasi
Dashboard Admin → Section baru **"👥 Riwayat Pemrosesan Surat"** (span penuh)

### Tabel Menampilkan
| Kolom | Deskripsi | Contoh |
|-------|-----------|--------|
| **Judul Surat** | Judul surat + tanggal pengajuan | "Surat Keputusan Pengangkatan..." (8 Apr 2026) |
| **Pengusul** | Nama karyawan/user yang mengajukan | "Budi Santoso" |
| **Status** | Status pemrosesan surat | ✓ Selesai / ✗ Ditolak / ● Proses |
| **Admin Pengolah** | Badge nama admin per tahapan | [Ari Kusuma] [Siti Nurhaida] [Admin D] |

### Badge Admin Pengolah
- Format: Badge biru dengan nama admin
- Hover/Tooltip: Menampilkan "Tahap X: Nama Tahapan"
- Multiple badges: Jika ada banyak admin yang proses
- "Belum ada yang proses": Jika tahapan belum disentuh admin

## 🔄 Data Flow

### Input Data
```
SuratTahapan (database)
├─ surat_id: Referensi surat
├─ tahap: 1-10 (tahap pemrosesan)
├─ nama_tahap: "Verifikasi Arsiparis", "Persetujuan Kepala Balai", dll
├─ status: 'selesai', 'proses', 'ditolak', 'menunggu'
├─ diproses_oleh: ID admin yang proses (user_id)
└─ diprosesByUser: Relasi ke User (nama admin)
```

### Processing
```
DashboardController::index()
├─ Query surat bulan ini dengan relasi tahapans
├─ Filter tahapan: status='selesai' AND diproses_oleh NOT NULL
├─ Eager load: user (pengusul) + tahapans.diprosesByUser
├─ Sort: terbaru duluan (orderByDesc created_at)
└─ Limit: 8 surat

View: dashboard.blade.php
├─ Looping surat dengan tahapans
├─ Display: judul, pengusul, status, admin pengolah
└─ Styling: Responsive table dengan badges
```

## 💾 Database Schema

### Tabel: `surat_tahapans`
```sql
CREATE TABLE surat_tahapans (
    id INT PRIMARY KEY,
    surat_id INT FOREIGN KEY,
    tahap INT (1-10),
    nama_tahap VARCHAR (Nama tahapan),
    status ENUM ('menunggu', 'proses', 'selesai', 'ditolak'),
    diproses_oleh INT FOREIGN KEY (users.id) -- Admin yang proses
    catatan TEXT,
    selesai_pada TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Contoh Data
```
| id | surat_id | tahap | nama_tahap | status | diproses_oleh | selesai_pada |
|----|----------|-------|-----------|--------|----------------|-------------|
| 1  | 5        | 1     | Usulan    | selesai| NULL          | 2026-04-08 |
| 2  | 5        | 2     | Verifikasi| selesai| 1 (Ari)       | 2026-04-08 |
| 3  | 5        | 3     | Verifikasi| selesai| 2 (Siti)      | 2026-04-08 |
| 4  | 5        | 4     | Persetujuan|selesai| 3 (Admin D)   | 2026-04-09 |
| 5  | 5        | 5     | Penomoran| proses | NULL          | NULL       |
```

## 🔌 Implementasi

### File Berubah
1. **`app/Http/Controllers/Admin/DashboardController.php`**
   - Tambah query `$suratDenganPengolah` dengan eager loading

2. **`resources/views/admin/dashboard.blade.php`**
   - Tambah section baru setelah "Surat Terbaru"
   - Menampilkan tabel dengan badge admin

### Query Optimization
```php
$suratDenganPengolah = Surat::whereMonth('created_at', $bulanIni)
                             ->whereYear('created_at', $tahunIni)
                             ->with([
                                 'user',
                                 'tahapans' => function ($query) {
                                     $query->where('status', 'selesai')
                                           ->whereNotNull('diproses_oleh')
                                           ->with('diprosesByUser')
                                           ->orderBy('tahap');
                                 }
                             ])
                             ->orderByDesc('created_at')
                             ->limit(8)
                             ->get();
```

## 🎨 Styling

### CSS Classes
- `.badge badge-blue`: Warna biru untuk admin pengolah
- `.badge badge-green`: Warna hijau untuk status selesai
- `.badge badge-red`: Warna merah untuk status ditolak
- `.badge badge-amber`: Warna kuning untuk status proses

### Responsive
- Table di-wrap dengan `table-wrap` class
- Font size: 13px untuk baris, 11px untuk subtitle
- Wrap badges: `flex-wrap: wrap` agar responsif di mobile

## 📈 Use Cases

1. **Manajemen Admin**
   - Manager dapat melihat siapa saja yg aktif proses surat
   - Identifikasi bottleneck (admin dengan beban berlebih)

2. **Audit & Compliance**
   - Traceback siapa yg menangani setiap tahapan
   - Keperluan audit untuk tanda tangan/persetujuan

3. **Training & Development**
   - Identifikasi admin berpengalaman vs baru
   - Distribusi tugas lebih efektif

4. **SLA Monitoring**
   - Jika terlambat, lihat siapa di mana bottlenecknya
   - Reminder ke admin tertentu

## 🧪 Testing

### Manual Testing
1. Login sebagai admin
2. Buka dashboard (route: admin.dashboard)
3. Scroll ke bawah cari section "👥 Riwayat Pemrosesan Surat"
4. Verifikasi:
   - ✓ Tabel muncul dengan data
   - ✓ Nama admin muncul di kolom "Admin Pengolah"
   - ✓ Status surat ditampilkan dengan badge
   - ✓ Tooltip muncul saat hover pada badge admin

### Browser DevTools
```javascript
// Cek query yang dijalankan (di MySQL Query Log)
// Expected: 3-4 queries saja (bukan N+1)

// Query 1: Ambil surat
// Query 2: Ambil tahapans untuk setiap surat (dengan IN clause)
// Query 3: Ambil users (pengolah) untuk tahapans
```

## ⚠️ Known Limitations

1. **Periode Tetap**: Hanya bulan berjalan, tidak ada filter tanggal
2. **Tahapan Selesai Only**: Tidak tampil tahapan yang masih "proses" atau "menunggu"
3. **No History**: Hanya 8 surat terakhir, tidak bisa lihat history bulanan
4. **No Export**: Belum ada fitur export riwayat pemrosesan

## 🚀 Future Enhancements

- [ ] Filter by date range (custom report)
- [ ] Export to CSV/PDF
- [ ] Show all tahapans (include pending ones)
- [ ] Admin activity report (siapa paling banyak proses surat)
- [ ] Average processing time per admin
- [ ] Department-wise workload distribution

## 📞 Support

Jika ada pertanyaan atau bug report, silakan buat issue di repository.
