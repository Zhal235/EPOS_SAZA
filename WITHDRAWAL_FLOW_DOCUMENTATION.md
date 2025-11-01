# 📋 ALUR PENARIKAN DANA EPOS-SIMPELS

## 🔄 FLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        SISTEM EPOS (Kantin)                              │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ 1. Admin EPOS membuat request
                                    │    penarikan dana
                                    ▼
         ┌──────────────────────────────────────────┐
         │  EPOS Financial > Penarikan SIMPels      │
         │  - Pilih periode transaksi               │
         │  - Total: Rp XXX (dari transaksi RFID)   │
         │  - Metode: Bank Transfer / Tunai         │
         │  - Detail rekening (jika transfer)       │
         └──────────────────────────────────────────┘
                                    │
                                    │ 2. Submit request
                                    ▼
         ┌──────────────────────────────────────────┐
         │   POST /api/epos/withdrawal/request      │
         │   - withdrawal_number                    │
         │   - period_start & period_end            │
         │   - total_amount                         │
         │   - transactions[] (detail)              │
         │   - bank_name, account_number            │
         └──────────────────────────────────────────┘
                                    │
                                    │ 3. Data dikirim ke SIMPels
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                      SISTEM SIMPELS (Bendahara)                          │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ 4. Request tercatat
                                    ▼
         ┌──────────────────────────────────────────┐
         │  Table: epos_withdrawals                 │
         │  Status: PENDING                         │
         │  - withdrawal_number: WD-20251014-001    │
         │  - total_amount: Rp 5,000,000            │
         │  - total_transactions: 125               │
         └──────────────────────────────────────────┘
                                    │
                                    │ 5. Admin SIMPels review
                                    ▼
         ┌──────────────────────────────────────────┐
         │  Keuangan > EPOS Penarikan               │
         │  Tab: "Request dari EPOS"                │
         │  - Lihat detail transaksi                │
         │  - Verifikasi jumlah                     │
         └──────────────────────────────────────────┘
                                    │
                          ┌─────────┴─────────┐
                          │                   │
                      SETUJUI              TOLAK
                          │                   │
                          ▼                   ▼
         ┌──────────────────────┐  ┌──────────────────────┐
         │  Status: APPROVED     │  │  Status: REJECTED    │
         │  approved_by          │  │  rejected_by         │
         │  approved_at          │  │  rejection_reason    │
         └──────────────────────┘  └──────────────────────┘
                          │                   │
                          │                   └──> Notif ke EPOS
                          │                        (Request Ditolak)
                          │
                          │ 6. Bendahara melakukan pembayaran
                          │    (Transfer Bank / Serahkan Tunai)
                          ▼
         ┌──────────────────────────────────────────┐
         │  Klik "Selesaikan Pembayaran"            │
         │  - Input bukti transfer                  │
         │  - Catatan pembayaran                    │
         └──────────────────────────────────────────┘
                          │
                          │ 7. Proses completion
                          ▼
         ┌──────────────────────────────────────────┐
         │  PROSES DI SIMPELS:                      │
         │  1. Update status: COMPLETED             │
         │  2. Buat TransaksiKas (pengeluaran)      │
         │  3. Kurangi saldo BukuKas Dompet Santri  │
         │  4. Record completed_by, completed_at    │
         └──────────────────────────────────────────┘
                          │
                          │ 8. Update status di EPOS
                          ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    KEMBALI KE EPOS (Notifikasi)                          │
└─────────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
         ┌──────────────────────────────────────────┐
         │  EPOS Update Status:                     │
         │  - Status: COMPLETED                     │
         │  - completed_at                          │
         │  - Dana sudah diterima                   │
         └──────────────────────────────────────────┘
                          │
                          ▼
                    ✅ SELESAI
```

---

## 📊 DATABASE SCHEMA

### **EPOS Database** (`simpels_withdrawals` table)
```sql
- id
- withdrawal_number (unique)
- period_start, period_end
- total_transactions
- total_amount
- status (pending/approved/rejected/completed)
- withdrawal_method (bank_transfer/cash)
- bank_name, account_number, account_name
- requested_by (user_id)
- simpels_withdrawal_id (foreign key ke SIMPels)
- notes
- timestamps
```

### **SIMPels Database** (`epos_withdrawals` table)
```sql
- id
- withdrawal_number (unique, dari EPOS)
- period_start, period_end
- total_transactions
- total_amount
- withdrawal_method
- bank_name, account_number, account_name
- requested_by (nama admin EPOS)
- notes
- status (pending/approved/rejected/completed)
- approved_by, approved_at, approval_notes
- rejected_by, rejected_at, rejection_reason
- completed_by, completed_at, payment_proof, completion_notes
- timestamps
```

### **SIMPels Database** (`epos_withdrawal_transactions` table)
```sql
- id
- epos_withdrawal_id (foreign key)
- transaction_number (dari EPOS)
- amount
- santri_id
- santri_name
- transaction_date
- timestamps
```

---

## 🔑 API ENDPOINTS

### **EPOS → SIMPels**

#### 1. Create Withdrawal Request
```
POST /api/epos/withdrawal/request
Authorization: Bearer {API_KEY}

Request Body:
{
  "withdrawal_number": "WD-20251014-001",
  "period_start": "2025-10-01",
  "period_end": "2025-10-14",
  "total_transactions": 125,
  "total_amount": 5000000,
  "withdrawal_method": "bank_transfer",
  "bank_name": "BCA",
  "account_number": "1234567890",
  "account_name": "Kantin Saza",
  "requested_by": "Admin EPOS",
  "notes": "Penarikan dana periode Oktober minggu ke-2",
  "transactions": [
    {
      "transaction_number": "TRX-001",
      "amount": 50000,
      "santri_id": "123",
      "santri_name": "Ahmad",
      "transaction_date": "2025-10-01"
    },
    ...
  ]
}

Response:
{
  "success": true,
  "message": "Withdrawal request created successfully",
  "data": {
    "id": 1,
    "withdrawal_number": "WD-20251014-001",
    "status": "pending",
    "total_amount": 5000000
  }
}
```

#### 2. Check Withdrawal Status
```
GET /api/epos/withdrawal/{id}
Authorization: Bearer {API_KEY}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "withdrawal_number": "WD-20251014-001",
    "status": "approved",
    "total_amount": 5000000,
    "approved_by": "Bendahara SIMPels",
    "approved_at": "2025-10-14 10:30:00"
  }
}
```

---

## 💻 CARA PENGGUNAAN

### **Di EPOS (Kantin)**

1. **Login** sebagai Admin/Manager
2. **Menu Financial** → Tab **"Penarikan SIMPels"**
3. Klik **"Tarik Dana"**
4. **Isi Form:**
   - Periode transaksi (start-end date)
   - Metode penarikan (Bank/Tunai)
   - Detail rekening (jika transfer)
   - Catatan
5. **Submit** → Request terkirim ke SIMPels
6. **Tunggu approval** dari admin SIMPels
7. **Status update** otomatis melalui API

### **Di SIMPels (Bendahara)**

1. **Login** sebagai Admin/Bendahara
2. **Menu Keuangan** → **EPOS Penarikan**
3. **Tab "Request dari EPOS"** → Lihat request yang masuk
4. **Klik "Lihat Detail"** → Verifikasi transaksi
5. **Pilih Action:**
   - **SETUJUI** → Jika valid
   - **TOLAK** → Jika ada masalah
6. Jika disetujui, **lakukan pembayaran** (transfer/tunai)
7. Klik **"Selesaikan Pembayaran"**
   - Input bukti pembayaran
   - Catatan
8. **Submit** → Dana tercatat keluar dari saldo dompet

---

## 📝 CATATAN PENTING

### ✅ **Validasi**
- Jumlah penarikan tidak boleh melebihi saldo tersedia
- Request harus disetujui sebelum bisa diselesaikan
- Hanya bisa approve request dengan status "pending"
- Hanya bisa complete request dengan status "approved"

### 🔒 **Security**
- Menggunakan API Key authentication
- Role-based access (Admin & Bendahara only)
- Logging semua aktivitas
- Transaction tracking

### 💾 **Data Consistency**
- Record tercatat di kedua database (EPOS & SIMPels)
- Status sinkronisasi real-time via API
- Backup transaction details

### 📊 **Reporting**
- Export laporan penarikan
- Riwayat lengkap dengan status
- Tracking approval chain

---

## 🐛 TROUBLESHOOTING

### **Request tidak terkirim**
- Cek koneksi API SIMPels
- Verifikasi API Key
- Check log di `storage/logs/laravel.log`

### **Status tidak update**
- Manual refresh halaman
- Implementasi polling/webhook untuk auto-update
- Check API response

### **Saldo tidak sesuai**
- Verifikasi perhitungan transaksi
- Cek filter periode
- Review TransaksiDompet dengan kategori "EPOS Payment"

---

## 📞 CONTACT SUPPORT

Jika ada masalah, hubungi:
- Tim IT SIMPels
- Admin EPOS
- Check documentation & logs
