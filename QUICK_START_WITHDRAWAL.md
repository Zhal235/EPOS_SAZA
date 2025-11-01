# 🚀 QUICK START - Penarikan Dana EPOS ke SIMPels

## ✅ Sudah Siap Digunakan!

Sistem penarikan dana dari EPOS ke SIMPels sudah terintegrasi penuh.

---

## 📍 **CARA MENGGUNAKAN**

### **A. DI EPOS (Kantin)** 🏪

1. **Login** sebagai Admin/Manager EPOS
2. Buka menu **"Financial"** di sidebar
3. Klik tab **"Penarikan SIMPels"**
4. Klik tombol **"Tarik Dana"**
5. **Isi form modal:**
   - **Tanggal Mulai & Akhir** (periode transaksi yang akan ditarik)
   - **Metode Penarikan**: Bank Transfer atau Tunai
   - **Detail Bank** (jika pilih transfer):
     - Nama Bank (contoh: BCA, Mandiri, BRI)
     - Nomor Rekening
     - Nama Pemegang Rekening
   - **Catatan** (opsional)
6. Klik **"Submit"**
7. ✅ Request terkirim ke SIMPels!
8. Tunggu persetujuan dari admin SIMPels

**Status akan update otomatis:**
- 🟡 **Pending** - Menunggu persetujuan
- 🔵 **Approved** - Disetujui, menunggu pembayaran
- ❌ **Rejected** - Ditolak
- ✅ **Completed** - Selesai, dana sudah diterima

---

### **B. DI SIMPELS (Bendahara)** 🏦

1. **Login** sebagai Admin/Bendahara SIMPels
2. Buka menu **"Keuangan" → "EPOS Penarikan"**
3. Lihat **section "📥 Permintaan Penarikan dari EPOS"**
4. **Review request:**
   - Lihat detail withdrawal number
   - Check periode transaksi
   - Verifikasi jumlah & rekening
   - Klik "Detail" untuk melihat list transaksi

5. **Pilih Action:**

   **a. SETUJUI:**
   - Klik tombol **"Setujui"**
   - Konfirmasi
   - Status berubah menjadi **"Disetujui"**

   **b. TOLAK:**
   - Klik tombol **"Tolak"**
   - Isi alasan penolakan (minimal 10 karakter)
   - Submit
   - Status berubah menjadi **"Ditolak"**

6. **Setelah disetujui, lakukan pembayaran:**
   - Transfer ke rekening yang tertera (jika bank transfer)
   - ATAU siapkan uang tunai
   
7. **Selesaikan pembayaran:**
   - Klik tombol **"Selesaikan Pembayaran"**
   - Isi:
     - Bukti Pembayaran (No. Referensi transfer)
     - Catatan
   - Submit
   
8. ✅ **Otomatis tercatat:**
   - Status withdrawal: **Completed**
   - Tercatat di **TransaksiKas** (pengeluaran)
   - Saldo **BukuKas Dompet Santri** berkurang
   - EPOS menerima notifikasi dana sudah dibayar

---

## 📊 **CONTOH SKENARIO**

### Scenario 1: Transfer Bank

```
EPOS membuat request:
- Periode: 1-14 Oktober 2025
- Total: Rp 5,000,000 (125 transaksi)
- Metode: Bank Transfer
- Bank: BCA
- No. Rek: 1234567890
- A/n: Kantin Saza

→ SIMPels menerima request
→ Bendahara review & SETUJUI
→ Bendahara transfer Rp 5,000,000 ke rekening BCA
→ Bendahara klik "Selesaikan" dengan bukti TRF-20251014-001
→ Dana tercatat keluar dari kas SIMPels
→ EPOS update status: COMPLETED
→ ✅ Selesai
```

### Scenario 2: Tunai

```
EPOS membuat request:
- Periode: 1-7 Oktober 2025
- Total: Rp 2,500,000 (80 transaksi)
- Metode: Tunai

→ SIMPels menerima request
→ Bendahara review & SETUJUI
→ Bendahara siapkan uang tunai Rp 2,500,000
→ Serahkan ke perwakilan kantin
→ Bendahara klik "Selesaikan"
→ Dana tercatat keluar dari kas SIMPels
→ EPOS update status: COMPLETED
→ ✅ Selesai
```

---

## 🔍 **CEK STATUS DI EPOS**

Di halaman **Financial → Tab Penarikan SIMPels**, Anda dapat melihat:

- ✅ **List semua withdrawal requests**
- 📊 **Status terkini** (Pending/Approved/Rejected/Completed)
- 📅 **Tanggal request & approval**
- 💰 **Jumlah dana**
- 👤 **Siapa yang approve/reject**
- 📝 **Catatan & alasan**

---

## ⚠️ **PENTING!**

### ✅ **DO's:**
- Pastikan periode transaksi benar
- Double-check detail rekening
- Verifikasi jumlah sebelum approve
- Simpan bukti transfer

### ❌ **DON'Ts:**
- Jangan approve tanpa verifikasi
- Jangan selesaikan sebelum pembayaran benar-benar dilakukan
- Jangan lupa isi bukti pembayaran

---

## 🐛 **TROUBLESHOOTING**

### "Tidak ada transaksi yang bisa ditarik pada periode ini"
- ✅ Cek apakah ada transaksi RFID di periode tersebut
- ✅ Pastikan transaksi sudah status "completed"
- ✅ Cek apakah transaksi belum pernah ditarik sebelumnya

### "Request tidak terkirim ke SIMPels"
- ✅ Cek koneksi internet
- ✅ Pastikan SIMPels server online
- ✅ Cek API key di config EPOS

### "Status tidak update"
- ✅ Refresh halaman
- ✅ Cek log di `storage/logs/laravel.log`

---

## 📞 **BUTUH BANTUAN?**

Hubungi:
- 📧 IT Support SIMPels
- 📱 Admin EPOS
- 📖 Lihat log untuk detail error

---

**Selamat Menggunakan! 🎉**
