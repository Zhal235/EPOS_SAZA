# Panduan Fitur Multi-Unit Penjualan

## Overview
Fitur multi-unit memungkinkan Anda menjual produk yang sama dalam berbagai satuan dengan harga berbeda. 
Contoh: Air mineral bisa dijual per botol (satuan) atau per dus (isi 24 botol).

## Cara Menggunakan

### 1. Mengelola Unit Produk

#### Di Halaman Products:
1. Buka menu **Products**
2. Cari produk yang ingin dikelola unitnya
3. Klik tombol **ikon timbangan (balance-scale)** berwarna ungu pada produk tersebut
4. Modal "Kelola Unit Penjualan" akan terbuka

#### Menambah Unit Baru:
1. Klik tombol **"Tambah Unit Baru"**
2. Isi form:
   - **Nama Unit**: Contoh: Dus, Box, Karton, Lusin
   - **Konversi ke Unit Dasar**: Berapa banyak unit dasar dalam 1 unit ini
     - Contoh: 1 Dus = 24 Pcs
   - **Harga Jual**: Harga jual untuk unit ini
   - **Harga Beli** (opsional): Harga beli untuk unit ini
   - **Harga Grosir** (opsional): Harga grosir untuk unit ini
   - **Barcode** (opsional): Barcode khusus untuk unit ini (misalnya barcode di kemasan dus)
   - **Unit Dasar**: Centang jika ini adalah unit terkecil
   - **Aktif**: Centang untuk mengaktifkan unit

3. Klik **"Simpan Unit"**

#### Mengedit/Menghapus Unit:
- Klik tombol **Edit (biru)** untuk mengubah unit
- Klik tombol **Hapus (merah)** untuk menghapus unit
- Klik tombol **Toggle (hijau/abu)** untuk mengaktifkan/nonaktifkan unit

### 2. Menjual dengan Unit di POS

#### Saat menambahkan produk ke keranjang:
1. Buka **POS Terminal**
2. Klik produk yang memiliki multiple unit
3. Modal "Pilih Unit Penjualan" akan muncul
4. Pilih unit yang sesuai (misal: Pcs atau Dus)
5. Produk akan ditambahkan ke keranjang dengan unit yang dipilih

#### Di Keranjang:
- Nama produk akan menampilkan unit yang dipilih, contoh: "Air Mineral (Dus)"
- Harga yang ditampilkan adalah harga per unit yang dipilih
- Stok akan otomatis dikalkulasi berdasarkan konversi unit

### 3. Stok Management

- Stok tetap disimpan dalam **satuan terkecil** (unit dasar)
- Saat menjual dengan unit besar (misal 1 Dus), stok akan otomatis dikurangi sesuai konversi
  - Contoh: Jual 1 Dus (konversi 24 pcs) = Stok berkurang 24 pcs
- Stok yang tersedia untuk unit besar dihitung otomatis:
  - Jika stok 100 pcs, maka tersedia 4 Dus (100 ÷ 24 = 4 sisa 4)

## Contoh Kasus Penggunaan

### Air Mineral Gelas
- **Unit Dasar**: Gelas (1)
- **Unit Tambahan**: 
  - Dus: 1 Dus = 48 Gelas, Harga Rp 45.000
  - Karton: 1 Karton = 240 Gelas, Harga Rp 220.000

### Air Mineral Botol
- **Unit Dasar**: Botol (1), Harga Rp 3.000
- **Unit Tambahan**: 
  - Dus: 1 Dus = 24 Botol, Harga Rp 70.000

### Minyak Goreng
- **Unit Dasar**: Sachet (1), Harga Rp 8.000
- **Unit Tambahan**: 
  - Dus: 1 Dus = 20 Sachet, Harga Rp 155.000
  - Karton: 1 Karton = 120 Sachet, Harga Rp 920.000

## Keuntungan

1. **Fleksibilitas**: Jual produk dalam satuan kecil atau besar
2. **Efisiensi**: Tidak perlu membuat produk terpisah untuk setiap satuan
3. **Akurasi Stok**: Stok terpusat dan otomatis terhitung
4. **Harga Berbeda**: Setiap unit bisa punya harga sendiri (harga grosir untuk unit besar)
5. **Barcode Terpisah**: Setiap unit bisa punya barcode sendiri untuk scan yang mudah

## Tips

- Pastikan konversi rate benar agar stok akurat
- Gunakan unit dasar untuk unit terkecil yang biasa dijual
- Beri nama unit yang jelas dan mudah dipahami kasir
- Test dulu dengan produk sampel sebelum implementasi penuh
