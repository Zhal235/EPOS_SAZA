# Integrasi EPOS-SIMPels: Customer Management

## Deskripsi
Dokumentasi ini menjelaskan integrasi antara aplikasi EPOS (Electronic Point of Sale) dengan sistem SIMPels untuk sinkronisasi data santri dan guru ke dalam menu Customer Management.

## Fitur yang Diimplementasikan

### 1. Endpoint API Baru di SIMPels
- **GET** `/api/epos/santri/all` - Mengambil semua data santri
- **GET** `/api/epos/guru/all` - Mengambil semua data guru
- **GET** `/api/epos/santri/rfid/{tag}` - Mencari santri berdasarkan RFID
- **GET** `/api/epos/guru/rfid/{tag}` - Mencari guru berdasarkan RFID

### 2. Service Layer di EPOS
- **SimpelsApiService** - Service untuk berkomunikasi dengan API SIMPels
- Caching mechanism untuk performa yang lebih baik
- Error handling dan logging yang komprehensif
- Health check untuk monitoring koneksi

### 3. Customer Management UI
- **Tab-based interface**: Umum, Santri, Guru
- **Sync functionality** untuk menarik data dari SIMPels
- **Real-time filtering** dan pencarian
- **Responsive design** dengan Tailwind CSS

## Struktur File yang Dimodifikasi/Ditambahkan

### SIMPels (Backend API)
```
app/Http/Controllers/API/
├── SantriEPOSController.php (modified)
└── GuruEPOSController.php (new)

routes/
└── api.php (modified)
```

### EPOS (Frontend/Consumer)
```
app/
├── Services/
│   └── SimpelsApiService.php (new)
└── Livewire/
    └── Customers.php (modified)

config/
└── services.php (modified)

resources/views/livewire/
└── customers.blade.php (new)

database/migrations/
└── 2025_09_23_083215_update_customer_type_enum_in_users_table.php (new)

tests/Feature/
└── SimpelsIntegrationTest.php (new)

integration_test.php (new)
.env.example (modified)
```

## Konfigurasi

### Environment Variables
Tambahkan ke file `.env` di aplikasi EPOS:

```env
# SIMPels Integration Configuration
SIMPELS_API_URL=http://localhost:8000/api
SIMPELS_API_TIMEOUT=30
SIMPELS_API_KEY=
```

### Database Migration
Jalankan migration untuk memperbarui enum customer_type:

```bash
php artisan migrate
```

## Cara Penggunaan

### 1. Menjalankan Aplikasi
Pastikan kedua aplikasi berjalan:

**SIMPels:**
```bash
cd /path/to/SIMPels
php artisan serve --port=8000
```

**EPOS:**
```bash
cd /path/to/EPOS_SAZA
php artisan serve --port=8001
```

### 2. Mengakses Customer Management
1. Login ke aplikasi EPOS
2. Navigasi ke menu "Customers"
3. Pilih tab "Santri" atau "Guru"
4. Klik tombol "Test SIMPels Connection" untuk memverifikasi koneksi
5. Klik tombol "Sync from SIMPels" untuk menarik data

### 3. Testing Integrasi
Jalankan automated test:

```bash
php artisan test tests/Feature/SimpelsIntegrationTest.php
```

Atau gunakan manual test script:

```bash
php artisan tinker
include_once('integration_test.php');
testSimpelsIntegration();
```

## API Response Format

### Santri Data Structure
```json
{
  "success": true,
  "message": "Data santri berhasil diambil",
  "data": [
    {
      "id": 1,
      "nis": "2023001",
      "nama_santri": "Ahmad Santri",
      "email": "ahmad@test.com",
      "no_hp": "081234567890",
      "kelas": "7A",
      "asrama": "Asrama Putra 1",
      "rfid_tag": "RF001",
      "saldo": 50000,
      "limit_harian": 50000,
      "status": "aktif",
      "foto": null
    }
  ],
  "total": 1
}
```

### Guru Data Structure
```json
{
  "success": true,
  "message": "Data guru berhasil diambil",
  "data": [
    {
      "id": 1,
      "nip": "197001011999031001",
      "nama_guru": "Dr. Budi Guru",
      "email": "budi@test.com",
      "no_hp": "081234567891",
      "mata_pelajaran": "Matematika",
      "pengalaman_tahun": 10,
      "rfid_tag": "RF002",
      "status": "aktif",
      "foto": null
    }
  ],
  "total": 1
}
```

## Error Handling

### Common Errors
1. **Connection Timeout**: Pastikan SIMPels server berjalan
2. **API Endpoint Not Found**: Verifikasi routing di SIMPels
3. **Database Migration Error**: Jalankan migration dengan benar
4. **Permission Error**: Pastikan user memiliki akses ke menu customers

### Logging
Semua aktivitas API dicatat di:
- `storage/logs/laravel.log` (EPOS)
- Error handling dengan try-catch pada setiap operasi

## Performance Optimization

### Caching Strategy
- Data santri/guru di-cache selama 5 menit
- Cache otomatis di-clear setelah sync berhasil
- Manual cache clearing tersedia melalui `SimpelsApiService::clearCache()`

### Database Indexing
- Index pada kolom `customer_type` dan `rfid_number`
- Unique constraint pada `rfid_number`

## Security Considerations

### API Security
- Implementasi API key authentication (opsional)
- CORS configuration untuk cross-origin requests
- Input validation pada semua endpoint

### Data Privacy
- Password default untuk santri: 'santri123'
- Password default untuk guru: 'guru123'
- Email fallback: `{nis}@santri.simpels.local` atau `{nip}@guru.simpels.local`

## Monitoring & Maintenance

### Health Check
Gunakan endpoint health check untuk monitoring:
```php
$service = new SimpelsApiService();
$status = $service->getHealthStatus();
```

### Regular Maintenance
1. Monitor API response time
2. Clear cache secara berkala jika diperlukan
3. Update timeout configuration sesuai network latency
4. Backup data sebelum sync besar-besaran

## Troubleshooting

### Issue: "Connection refused"
**Solution:** Pastikan SIMPels server berjalan di port yang benar

### Issue: "Class not found SimpelsApiService"
**Solution:** Jalankan `composer dump-autoload`

### Issue: "Column 'customer_type' doesn't exist"
**Solution:** Jalankan migration database yang missing

### Issue: "RFID duplicate entry"
**Solution:** Bersihkan data duplikat sebelum sync

## Future Enhancements

1. **Real-time sync** menggunakan WebSocket atau polling
2. **Batch processing** untuk sync data dalam jumlah besar
3. **Conflict resolution** untuk data yang berubah di kedua sistem
4. **Audit trail** untuk tracking perubahan data
5. **Role-based access** untuk pembatasan sync berdasarkan user role

## Support

Untuk pertanyaan atau issue, silakan contact developer atau buat issue di repository project.

---
*Dokumentasi ini dibuat pada: September 23, 2025*
*Versi integrasi: 1.0*