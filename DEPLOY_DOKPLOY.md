# Panduan Deploy EPOS SAZA ke Dokploy

Panduan ini menjelaskan cara men-deploy aplikasi EPOS SAZA menggunakan Dokploy, serta menghubungkannya dengan infrastruktur `saza-network` dan SIMPELS yang sudah ada.

## Prasyarat

1.  **Server Dokploy**: Anda memiliki akses ke panel Dokploy.
2.  **Repository GitHub**: Repository ini sudah dipush ke GitHub/GitLab.
3.  **Network**: Pastikan `saza-network` sudah ada di server (Cek di Dokploy > Network, atau via CLI).

## Langkah 1: Buat Proyek

1.  Buka **Dashboard Dokploy** Anda.
2.  Masuk ke menu **Projects** dan buat proyek baru (misal: `epos-saza`).
3.  Buka proyek tersebut.

## Langkah 2: Konfigurasi Service (Docker Compose)

Kita akan menggunakan tipe deployment **Compose** karena kita membutuhkan beberapa service (App, DB, Redis) yang bekerja bersamaan.

1.  Klik tombol **"Compose"** (atau "Stack" tergantung versi Dokploy).
2.  Beri nama: `epos-stack`.
3.  **Pengaturan Repository**:
    *   **Provider**: GitHub.
    *   **Repository**: Pilih `Rhezal/EPOS_SAZA` (atau nama repo Anda).
    *   **Branch**: `main` (atau branch produksi Anda).
    *   **Build Path**: `/` (root).
    *   **Compose File Path**: `docker-compose.yml`.

## Langkah 3: Konfigurasi Environment Variables

1.  Masuk ke tab **Environment** pada service Compose tersebut.
2.  Salin isi dari file `.env.docker.example` yang ada di repository ini.
3.  Tempelkan ke editor Environment Variables di Dokploy.
4.  **Penting untuk Diubah**:
    *   `APP_KEY`: Generate key baru atau gunakan dari `.env` lokal Anda.
    *   `DB_PASSWORD`: Set password yang kuat.
    *   `MYSQL_ROOT_PASSWORD`: Samakan dengan `DB_PASSWORD`.
    *   `SIMPELS_API_URL`: Gunakan alamat internal container (`http://simpelssaza-simpelsapi-2ebzdr:8000/...`).

## Langkah 4: Konfigurasi Storage (Volumes)

Dokploy menangani volume yang didefinisikan di `docker-compose.yml`.
*   File compose kita menggunakan **named volumes** (`epos-mysql-data` dan `epos-redis-data`). Data ini akan tersimpan otomatis dan aman.
*   **Penting**: `epos-app` menggunakan volume mount (contoh: `./storage`).
    *   *Catatan*: Jika Dokploy melakukan clone ulang repo saat deploy, direktori bind mount lokal (`./storage`) mungkin akan tereset jika tidak ditangani dengan benar oleh sistem volume Dokploy.
    *   **Rekomendasi Dokploy**: Untuk memastikan persistensi file upload (gambar, avatar), disarankan untuk mengganti volume aplikasi di `docker-compose.yml` menjadi **named volume** atau menggunakan managed storage Dokploy. Atau lebih baik lagi, gunakan layanan S3 (AWS/MinIO) untuk `FILESYSTEM_DISK` di environment production.

## Langkah 5: Konfigurasi Network

1.  File `docker-compose.yml` kita mendeklarasikan:
    ```yaml
    networks:
      saza-network:
        external: true
    ```
2.  **Verifikasi**: Pastikan network bernama `saza-network` ada di mesin host. Jika SIMPELS sudah berjalan di sana, berarti network tersebut sudah ada.

## Langkah 6: Deploy

1.  Klik tombol **Deploy**.
2.  Masuk ke tab **Logs** dan pantau proses build.
    *   Sistem akan menginstall dependency Composer.
    *   Sistem akan membuild asset Vite (Node.js).
    *   Sistem akan menjalankan container.

## Langkah 7: Domain & SSL (Traefik)

Karena kita sudah menyertakan label Traefik di `docker-compose.yml`, instance Traefik di Dokploy akan mendeteksinya secara otomatis.

1.  **Label yang disertakan**:
    *   `Host(\`epos.saza.sch.id\`)`
    *   `entrypoints=websecure`
    *   `certresolver=letsencrypt` (sesuaikan dengan nama resolver di traefik global Anda)
2.  **DNS**: Pastikan domain `epos.saza.sch.id` sudah diarahkan (A record) ke IP server Dokploy Anda.
3.  Tunggu sejenak hingga Let's Encrypt men-generate sertifikat SSL.

## Pemecahan Masalah (Troubleshooting)

*   **Database Gagal Connect**:
    *   Pastikan `DB_HOST=epos-db` di environment variables.
    *   Periksa log dari container `epos-db`.
*   **Koneksi ke SIMPELS Gagal**:
    *   Masuk ke shell container app: `docker exec -it saza-epos-app sh`.
    *   Coba ping container SIMPELS: `ping simpelssaza-simpelsapi-2ebzdr`. Jika gagal, cek nama network.
*   **Error Permission pada Storage**:
    *   Jika muncul error "The stream or file ... could not be opened", jalankan perintah ini di dalam container atau console Dokploy:
        ```bash
        php artisan storage:link
        chown -R www-data:www-data storage
        ```

## Manajemen Database

Untuk mengakses database secara aman:
1.  Gunakan tool seperti TablePlus atau DBeaver.
2.  Lakukan koneksi via **SSH Tunnel** ke server Dokploy Anda.
3.  Host Database: `127.0.0.1` (relatif terhadap server), Port: `3306` (Internal container).
    *   *Tips*: Jika perlu akses langsung, Anda bisa mengekspos port sementara dengan menambahkan `ports: - "3307:3306"` pada service `epos-db` di `docker-compose.yml`, lalu deploy ulang. Jangan lupa hapus kembali untuk keamanan.
