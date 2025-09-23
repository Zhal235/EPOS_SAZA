<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Mock SIMPels API endpoints for testing
Route::prefix('api/epos')->group(function () {
    
    // Get all santri
    Route::get('/santri/all', function () {
        return response()->json([
            'success' => true,
            'message' => 'Data santri berhasil ditemukan',
            'data' => [
                [
                    'id' => 1,
                    'nis' => 'TEST001',
                    'nama_santri' => 'Ahmad Test Santri',
                    'kelas' => 'XII IPA 1',
                    'asrama' => 'Asrama Putra A',
                    'rfid_tag' => 'TEST123456789',
                    'saldo' => 100000,
                    'limit_harian' => 50000,
                    'status' => 'aktif',
                    'email' => 'test.santri@simpels.local',
                    'no_hp' => '081234567890'
                ],
                [
                    'id' => 2,
                    'nis' => 'TEST002',
                    'nama_santri' => 'Budi Test Santri',
                    'kelas' => 'XI IPS 2',
                    'asrama' => 'Asrama Putra B',
                    'rfid_tag' => 'TEST123456788',
                    'saldo' => 5000,
                    'limit_harian' => 25000,
                    'status' => 'aktif',
                    'email' => 'test.santri2@simpels.local',
                    'no_hp' => '081234567891'
                ],
                [
                    'id' => 3,
                    'nis' => 'TEST003',
                    'nama_santri' => 'Citra Test Santri',
                    'kelas' => 'X A',
                    'asrama' => 'Asrama Putri A',
                    'rfid_tag' => 'TEST123456787',
                    'saldo' => 75000,
                    'limit_harian' => 30000,
                    'status' => 'aktif',
                    'email' => 'test.santri3@simpels.local',
                    'no_hp' => '081234567892'
                ],
                [
                    'id' => 4,
                    'nis' => 'SAN001',
                    'nama_santri' => 'Muhammad Rizky Hidayat',
                    'kelas' => 'XII IPA 2',
                    'asrama' => 'Asrama Putra C',
                    'rfid_tag' => 'RFD001234567',
                    'saldo' => 150000,
                    'limit_harian' => 75000,
                    'status' => 'aktif',
                    'email' => 'rizky@simpels.local',
                    'no_hp' => '082345678901'
                ],
                [
                    'id' => 5,
                    'nis' => 'SAN002',
                    'nama_santri' => 'Fatimah Zahra',
                    'kelas' => 'XI IPS 1',
                    'asrama' => 'Asrama Putri B',
                    'rfid_tag' => 'RFD001234568',
                    'saldo' => 85000,
                    'limit_harian' => 40000,
                    'status' => 'aktif',
                    'email' => 'fatimah@simpels.local',
                    'no_hp' => '083456789012'
                ]
            ]
        ]);
    });
    
    // Get all guru
    Route::get('/guru/all', function () {
        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil ditemukan',
            'data' => [
                [
                    'id' => 1,
                    'nip' => 'GRU001',
                    'nama_guru' => 'Drs. Ahmad Wijaya, M.Pd',
                    'mata_pelajaran' => 'Matematika',
                    'pengalaman_tahun' => 15,
                    'rfid_tag' => 'GRU123456789',
                    'status' => 'aktif',
                    'email' => 'ahmad.wijaya@simpels.local',
                    'no_hp' => '081234567800'
                ],
                [
                    'id' => 2,
                    'nip' => 'GRU002',
                    'nama_guru' => 'Dr. Siti Nurhaliza, S.Pd, M.A',
                    'mata_pelajaran' => 'Bahasa Indonesia',
                    'pengalaman_tahun' => 12,
                    'rfid_tag' => 'GRU123456788',
                    'status' => 'aktif',
                    'email' => 'siti.nurhaliza@simpels.local',
                    'no_hp' => '081234567801'
                ],
                [
                    'id' => 3,
                    'nip' => 'GRU003',
                    'nama_guru' => 'H. Bambang Sutrisno, S.Si, M.Pd',
                    'mata_pelajaran' => 'IPA',
                    'pengalaman_tahun' => 20,
                    'rfid_tag' => 'GRU123456787',
                    'status' => 'aktif',
                    'email' => 'bambang.sutrisno@simpels.local',
                    'no_hp' => '081234567802'
                ],
                [
                    'id' => 4,
                    'nip' => 'GRU004',
                    'nama_guru' => 'Hj. Dewi Kartika, S.Sos, M.Pd',
                    'mata_pelajaran' => 'IPS',
                    'pengalaman_tahun' => 8,
                    'rfid_tag' => 'GRU123456786',
                    'status' => 'aktif',
                    'email' => 'dewi.kartika@simpels.local',
                    'no_hp' => '081234567803'
                ],
                [
                    'id' => 5,
                    'nip' => 'GRU005',
                    'nama_guru' => 'Ustadz Muhammad Farid, Lc',
                    'mata_pelajaran' => 'Pendidikan Agama',
                    'pengalaman_tahun' => 10,
                    'rfid_tag' => 'GRU123456785',
                    'status' => 'aktif',
                    'email' => 'muhammad.farid@simpels.local',
                    'no_hp' => '081234567804'
                ]
            ]
        ]);
    });
    
    // Get santri by RFID (existing endpoint)
    Route::get('/santri/rfid/{tag}', function ($tag) {
        $santriData = [
            'TEST123456789' => [
                'id' => 1,
                'nis' => 'TEST001',
                'nama_santri' => 'Ahmad Test Santri',
                'kelas' => 'XII IPA 1',
                'asrama' => 'Asrama Putra A',
                'rfid_tag' => 'TEST123456789',
                'saldo' => 100000,
                'status' => 'aktif',
                'foto' => 'storage/santri/photos/1.jpg'
            ],
            'TEST123456788' => [
                'id' => 2,
                'nis' => 'TEST002',
                'nama_santri' => 'Budi Test Santri',
                'kelas' => 'XI IPS 2',
                'asrama' => 'Asrama Putra B',
                'rfid_tag' => 'TEST123456788',
                'saldo' => 5000,
                'status' => 'aktif',
                'foto' => 'storage/santri/photos/2.jpg'
            ],
            'TEST123456787' => [
                'id' => 3,
                'nis' => 'TEST003',
                'nama_santri' => 'Citra Test Santri',
                'kelas' => 'X A',
                'asrama' => 'Asrama Putri A',
                'rfid_tag' => 'TEST123456787',
                'saldo' => 75000,
                'status' => 'aktif',
                'foto' => 'storage/santri/photos/3.jpg'
            ]
        ];
        
        if (isset($santriData[$tag])) {
            return response()->json([
                'success' => true,
                'message' => 'Data santri berhasil ditemukan',
                'data' => $santriData[$tag]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'RFID tag tidak ditemukan atau tidak aktif'
        ], 404);
    });
    
    // Check transaction limit
    Route::post('/limit/check-rfid', function (Request $request) {
        $rfidTag = $request->input('rfid_tag');
        $amount = $request->input('amount');
        
        // Mock limit checking
        $limits = [
            'TEST123456789' => 50000,
            'TEST123456788' => 25000,
            'TEST123456787' => 30000
        ];
        
        $limit = $limits[$rfidTag] ?? 50000;
        
        if ($amount > $limit) {
            return response()->json([
                'success' => false,
                'message' => "Transaksi melebihi limit harian. Limit: Rp " . number_format($limit, 0, ',', '.')
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Transaksi dalam batas limit'
        ]);
    });
    
    // Deduct balance
    Route::post('/santri/{id}/deduct', function (Request $request, $id) {
        // Mock deduction process
        $nominal = $request->input('nominal');
        $keterangan = $request->input('keterangan');
        $transactionRef = $request->input('transaction_ref');
        
        // Simulate successful deduction
        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil dipotong',
            'data' => [
                'id' => rand(1000, 9999),
                'saldo_sebelum' => 100000,
                'saldo_sesudah' => 100000 - $nominal,
                'nominal' => $nominal,
                'keterangan' => $keterangan,
                'transaction_ref' => $transactionRef,
                'timestamp' => now()->toISOString()
            ]
        ]);
    });
    
    // Process refund
    Route::post('/santri/{id}/refund', function (Request $request, $id) {
        // Mock refund process
        $nominal = $request->input('nominal');
        $originalTransactionRef = $request->input('original_transaction_ref');
        $refundReason = $request->input('refund_reason');
        
        return response()->json([
            'success' => true,
            'message' => 'Refund berhasil diproses',
            'data' => [
                'id' => rand(1000, 9999),
                'saldo_sebelum' => 75000,
                'saldo_sesudah' => 75000 + $nominal,
                'nominal' => $nominal,
                'original_transaction_ref' => $originalTransactionRef,
                'refund_reason' => $refundReason,
                'timestamp' => now()->toISOString()
            ]
        ]);
    });
    
    // Sync transaction
    Route::post('/transaction/sync', function (Request $request) {
        // Mock transaction sync
        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disync',
            'data' => [
                'sync_id' => rand(10000, 99999),
                'epos_transaction_id' => $request->input('epos_transaction_id'),
                'santri_id' => $request->input('santri_id'),
                'total_amount' => $request->input('total_amount'),
                'items' => $request->input('items'),
                'synced_at' => now()->toISOString()
            ]
        ]);
    });
    
    // Limit summary
    Route::get('/limit/summary', function () {
        return response()->json([
            'success' => true,
            'message' => 'Summary limit berhasil dimuat',
            'data' => [
                'total_santri' => 150,
                'total_transactions_today' => 45,
                'total_amount_today' => 2500000,
                'server_status' => 'online',
                'last_sync' => now()->toISOString()
            ]
        ]);
    });
});