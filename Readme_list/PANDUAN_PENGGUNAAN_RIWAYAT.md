# 📖 Panduan Penggunaan: Fitur Riwayat Pemrosesan Surat

## 👋 Untuk Siapa?

Fitur ini dirancang untuk:
- **Admin/Manager**: Monitoring alur pemrosesan dan akuntabilitas tim
- **Audit/Compliance**: Tracking siapa memproses apa dan kapan
- **HR/Pimpinan**: Evaluasi kinerja admin berdasarkan volume pekerjaan

---

## 🎯 Apa Fungsinya?

Menampilkan **daftar admin yang telah memproses setiap surat** dengan detail tahapan, sehingga Anda bisa:

✅ Tahu siapa mengerjakan apa
✅ Audit trail yang jelas untuk compliance
✅ Identifikasi bottleneck dalam proses
✅ Monitor beban kerja per admin
✅ Laporan akuntabilitas tim

---

## 📍 Di Mana Menemukannya?

### Akses
```
1. Login ke dashboard admin
2. Dashboard → Scroll ke bawah
3. Cari section "👥 Riwayat Pemrosesan Surat"
```

### URL Langsung
```
/admin/dashboard
```

---

## 📊 Cara Membaca Tabel

### Kolom 1: Judul Surat
```
┌─ Surat Keputusan Pengangkatan...
└─ 8 Apr 2026
```
- **Baris 1**: Judul surat (dipotong jika terlalu panjang)
- **Baris 2**: Tanggal pengajuan surat

### Kolom 2: Pengusul
```
Budi Santoso
```
- Nama karyawan/user yang mengajukan surat

### Kolom 3: Status
```
✓ Selesai   (hijau)
✗ Ditolak   (merah)
● Proses    (kuning)
```
- **Selesai**: Surat sudah selesai semua tahapan
- **Ditolak**: Surat ditolak di salah satu tahapan
- **Proses**: Surat masih dalam perjalanan (belum selesai)

### Kolom 4: Admin Pengolah
```
[Ari Kusuma] [Siti N.] [Admin D] [HR Manager]
```
- Badge dengan nama admin yang telah memproses
- Setiap badge mewakili satu tahapan

---

## 🔍 Hover untuk Detail Tahapan

Saat Anda **hover (tunjuk mouse)** ke badge admin, akan muncul tooltip yang menunjukkan:

```
[Ari Kusuma] ← Hover di sini
  ↓
Tooltip: "Tahap 2: Verifikasi Arsiparis"
```

**Artinya**: Ari Kusuma memproses di Tahap 2 (Verifikasi Arsiparis)

### Daftar Tahapan (1-10)
| Tahap | Nama | Pengolah Tipikal |
|-------|------|-----------------|
| 1 | Usulan Diajukan | - (User) |
| 2 | Verifikasi Arsiparis | Arsiparis |
| 3 | Verifikasi Kasubbag TU | Kasubbag TU |
| 4 | Persetujuan Kepala Balai | Kepala Balai |
| 5 | Penomoran Surat | Admin Persuratan |
| 6 | Tanda Tangan (DS) | Pejabat |
| 7 | Pengiriman via TNDe | Admin TNDe |
| 8 | Pengiriman via Srikandi | Admin Srikandi |
| 9 | Pengarsipan | Arsiparis |
| 10 | Follow Up / Selesai | Admin |

---

## 💡 Use Cases & Contoh

### Use Case 1: Monitoring Kinerja Harian

**Skenario**: Pagi hari sebelum rapat, manager ingin lihat siapa yang sudah memproses berapa surat.

**Langkah**:
1. Buka dashboard
2. Lihat tabel "Riwayat Pemrosesan Surat"
3. Lihat berapa kali nama masing-masing admin muncul
4. Admin yang muncul banyak = sedang banyak pekerjaan

**Contoh Output**:
```
Ari Kusuma: 4 surat (paling banyak) → Kemungkinan berat beban
Siti N.: 3 surat → Normal
Admin D: 2 surat → Kurang beban
```

---

### Use Case 2: Audit Surat Tertentu

**Skenario**: Kepala bagian ingin tahu siapa saja yang sudah tanda tangan di SK Pengangkatan.

**Langkah**:
1. Cari surat "SK Pengangkatan..." di tabel
2. Hover ke setiap badge admin untuk lihat detail tahapan
3. Lihat siapa yang di Tahap 6 (Tanda Tangan)

**Contoh**:
```
Surat: SK Pengangkatan Direksi
Admin Pengolah: [Ari K.] [Siti N.] [Admin D] [Pejabat XXX]

Hover [Pejabat XXX]:
→ Tahap 6: Tanda Tangan (DS) ✓
→ Berarti Pejabat XXX yang tanda tangan
```

---

### Use Case 3: Compliance & Laporan

**Skenario**: Audit eksternal minta bukti "siapa yang approve surat ini?"

**Langkah**:
1. Search surat di tabel → Lihat admin yang Tahap 4 (Persetujuan Kepala Balai)
2. Print/screenshot untuk laporan
3. Dokumentasi audit trail

**Contoh**:
```
Surat: Surat Dinas Penugasan
Status: ✓ Selesai
Admin Pengolah: [Ari K.] [Siti N.] [Kepala Balai A] [...]

Hover [Kepala Balai A]:
→ Tahap 4: Persetujuan Kepala Balai ✓
→ Bukti: Kepala Balai A approve surat ini
```

---

### Use Case 4: Identifikasi Bottleneck

**Skenario**: Banyak surat stuck di tahap 3, cari tahu kenapa.

**Langkah**:
1. Filter mental surat yang status "● Proses"
2. Lihat badge admin → Cek siapa yang tahap terakhir
3. Hubungi admin tersebut untuk follow up

**Contoh**:
```
[Surat A] ● Proses → [Ari K.] [Siti N.] ← Last (stuck di sini?)
[Surat B] ● Proses → [Ari K.] [Siti N.] ← Last (stuck di sini?)
[Surat C] ● Proses → [Ari K.] [Siti N.] ← Last (stuck di sini?)

Kesimpulan: Siti N. sedang congestion di Tahap 3
Action: Follow up ke Siti N. atau alihkan ke orang lain
```

---

## ⚙️ Tips & Trik

### Tip 1: Membaca Badge Admin
- **Urutan badge = Urutan tahap pemrosesan**
- Badge paling kiri = Tahap pertama
- Badge paling kanan = Tahap terakhir

### Tip 2: Membedakan Jenis Admin
```
[Ari Kusuma]     ← Admin biasa
[Kepala Balai A] ← Pejabat struktural
[HR Manager]     ← Dari dept lain
```

### Tip 3: Surat Tidak Ada Admin?
```
"Belum ada yang proses" = Surat belum disentuh tahap ini
(Biasanya karena masih menunggu di antrian)
```

### Tip 4: Hover Tooltip
- Jangan lupa hover ke badge untuk lihat detail tahapan
- Gunakan untuk verifikasi, bukan hanya lihat nama

---

## 🚀 FAQ

### Q1: Kenapa ada surat tapi tidak ada admin di kolom "Admin Pengolah"?

**A:** Surat itu baru masuk dan belum ada admin yang proses (masih di tahap "Menunggu").
Biasanya ini terjadi pada surat yang baru diajukan.

---

### Q2: Surat sudah "Selesai" tapi masih muncul di sini?

**A:** Itu normal! Section ini menampilkan SEMUA surat bulan ini yang sudah ada tahapan terselesaikan, termasuk yang sudah final.
Gunakan untuk tracking riwayat siapa yang proses.

---

### Q3: Kok admin XYZ tidak pernah muncul di tabel?

**A:** Ada beberapa kemungkinan:
- Admin belum proses surat bulan ini
- Admin baru/masih dalam masa percobaan
- Admin tidak assigned ke tahapan tertentu

**Solusi**: Cek langsung di "Antrian Menunggu Aksi" untuk lihat siapa yang seharusnya proses.

---

### Q4: Bisa filter by tanggal tertentu?

**A:** Fitur versi 1.0 hanya menampilkan bulan berjalan. 
Untuk laporan custom (bulan lalu, range tanggal), akan ada di fitur enhancement mendatang.
**Workaround**: Screenshot setiap akhir bulan untuk archive.

---

### Q5: Bisa export ke CSV/PDF?

**A:** Belum di versi 1.0. 
**Workaround**: Screenshot dan paste ke Excel, atau print langsung dari browser.
**Enhancement**: Fitur ini akan ditambah di update berikutnya.

---

## 📱 Akses dari Mobile

Tabel akan menyesuaikan layout untuk mobile:
- Kolom lebih sempit
- Font lebih kecil
- Badge admin akan wrap ke baris berikutnya
- Scroll horizontal jika perlu

**Rekomendasi**: Akses dari desktop untuk experience lebih baik.

---

## 🔒 Privacy & Security

✅ Hanya admin yang bisa lihat fitur ini
✅ Nama admin yang ditampilkan adalah data publik (sudah punya akses)
✅ Tidak ada sensible data yang di-expose
✅ Data terekam sesuai database, tidak bisa diubah

---

## 📞 Butuh Bantuan?

Jika ada pertanyaan atau menemukan bug:

1. **Internal**: Hubungi tim IT/Admin
2. **Bug Report**: Lihat file `CHANGELOG_RIWAYAT_PEMROSESAN.md`
3. **Feature Request**: Buka discussion di internal system

---

## 📋 Checklist Untuk Manager

Saat monitoring dashboard, pastikan:

- [ ] Semua surat bulan ini sudah muncul
- [ ] Admin yang assign muncul di badge
- [ ] Tidak ada surat yang "stuck" terlalu lama
- [ ] Beban kerja terlihat seimbang antar admin
- [ ] Status surat konsisten dengan fakta di lapangan

---

**Versi Dokumentasi:** 1.0
**Last Updated:** 8 April 2026
**Status:** Ready for Production
