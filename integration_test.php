<?php

/**
 * Script untuk testing integrasi EPOS-SIMPels
 * 
 * Jalankan dengan: php artisan tinker
 * Kemudian: include_once('integration_test.php');
 */

use App\Services\SimpelsApiService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

function testSimpelsIntegration()
{
    echo "=== Testing Integrasi EPOS-SIMPels ===\n\n";
    
    try {
        $service = new SimpelsApiService();
        
        // Test 1: Koneksi ke SIMPels
        echo "1. Testing koneksi ke SIMPels API...\n";
        $healthStatus = $service->getHealthStatus();
        
        if ($healthStatus['status'] === 'healthy') {
            echo "   ✅ Koneksi berhasil (Response time: {$healthStatus['response_time_ms']}ms)\n";
        } else {
            echo "   ❌ Koneksi gagal: {$healthStatus['error']}\n";
            return false;
        }
        
        // Test 2: Ambil data santri
        echo "\n2. Testing mengambil data santri...\n";
        $santriResponse = $service->getAllSantri(false);
        
        if ($santriResponse['success']) {
            $santriCount = count($santriResponse['data']);
            echo "   ✅ Berhasil mengambil {$santriCount} data santri\n";
            
            if ($santriCount > 0) {
                $firstSantri = $santriResponse['data'][0];
                echo "   Contoh data: {$firstSantri['nama_santri']} (NIS: {$firstSantri['nis']})\n";
            }
        } else {
            echo "   ❌ Gagal mengambil data santri: {$santriResponse['message']}\n";
        }
        
        // Test 3: Ambil data guru
        echo "\n3. Testing mengambil data guru...\n";
        $guruResponse = $service->getAllGuru(false);
        
        if ($guruResponse['success']) {
            $guruCount = count($guruResponse['data']);
            echo "   ✅ Berhasil mengambil {$guruCount} data guru\n";
            
            if ($guruCount > 0) {
                $firstGuru = $guruResponse['data'][0];
                echo "   Contoh data: {$firstGuru['nama_guru']} (NIP: {$firstGuru['nip']})\n";
            }
        } else {
            echo "   ❌ Gagal mengambil data guru: {$guruResponse['message']}\n";
        }
        
        // Test 4: Test cache
        echo "\n4. Testing cache sistem...\n";
        $start = microtime(true);
        $service->getAllSantri(true); // Use cache
        $cachedTime = (microtime(true) - $start) * 1000;
        
        $start = microtime(true);
        $service->getAllSantri(false); // Don't use cache
        $nonCachedTime = (microtime(true) - $start) * 1000;
        
        echo "   Cache response time: " . round($cachedTime, 2) . "ms\n";
        echo "   Non-cache response time: " . round($nonCachedTime, 2) . "ms\n";
        
        if ($cachedTime < $nonCachedTime) {
            echo "   ✅ Cache bekerja dengan baik\n";
        }
        
        // Test 5: Database connectivity
        echo "\n5. Testing database EPOS...\n";
        $userCount = User::count();
        $santriCount = User::where('customer_type', 'santri')->count();
        $guruCount = User::where('customer_type', 'guru')->count();
        
        echo "   Total users: {$userCount}\n";
        echo "   Santri: {$santriCount}\n";
        echo "   Guru: {$guruCount}\n";
        echo "   ✅ Database EPOS accessible\n";
        
        echo "\n=== Semua test berhasil! ===\n";
        return true;
        
    } catch (Exception $e) {
        echo "\n❌ Error during testing: " . $e->getMessage() . "\n";
        Log::error("Integration test failed: " . $e->getMessage());
        return false;
    }
}

function syncDataFromSimpels()
{
    echo "\n=== Sync Data dari SIMPels ===\n\n";
    
    try {
        $service = new SimpelsApiService();
        
        // Sync Santri
        echo "Syncing santri data...\n";
        $santriResponse = $service->getAllSantri(false);
        
        if ($santriResponse['success']) {
            $syncedCount = 0;
            $updatedCount = 0;
            
            foreach ($santriResponse['data'] as $santriData) {
                if (empty($santriData['nis']) || empty($santriData['nama_santri'])) {
                    continue;
                }
                
                $existingSantri = User::where('nis', $santriData['nis'])->first();
                
                $userData = [
                    'name' => $santriData['nama_santri'],
                    'email' => $santriData['email'] ?? $santriData['nis'] . '@santri.simpels.local',
                    'phone' => $santriData['no_hp'] ?? null,
                    'password' => bcrypt('santri123'),
                    'role' => 'customer',
                    'customer_type' => 'santri',
                    'nis' => $santriData['nis'],
                    'class' => $santriData['kelas'] ?? null,
                    'rfid_number' => $santriData['rfid_tag'] ?? null,
                    'balance' => $santriData['saldo'], // No fallback - must have actual balance from API
                    'spending_limit' => $santriData['limit_harian'] ?? 50000,
                    'is_active' => ($santriData['status'] ?? 'aktif') === 'aktif',
                ];
                
                if ($existingSantri) {
                    $existingSantri->update($userData);
                    $updatedCount++;
                } else {
                    User::create($userData);
                    $syncedCount++;
                }
            }
            
            echo "✅ Santri sync complete: {$syncedCount} new, {$updatedCount} updated\n";
        }
        
        // Sync Guru
        echo "Syncing guru data...\n";
        $guruResponse = $service->getAllGuru(false);
        
        if ($guruResponse['success']) {
            $syncedCount = 0;
            $updatedCount = 0;
            
            foreach ($guruResponse['data'] as $guruData) {
                if (empty($guruData['nip']) || empty($guruData['nama_guru'])) {
                    continue;
                }
                
                $existingGuru = User::where('nip', $guruData['nip'])->first();
                
                $userData = [
                    'name' => $guruData['nama_guru'],
                    'email' => $guruData['email'] ?? $guruData['nip'] . '@guru.simpels.local',
                    'phone' => $guruData['no_hp'] ?? null,
                    'password' => bcrypt('guru123'),
                    'role' => 'customer',
                    'customer_type' => 'guru',
                    'nip' => $guruData['nip'],
                    'subject' => $guruData['mata_pelajaran'] ?? null,
                    'experience' => $guruData['pengalaman_tahun'] ?? 0,
                    'rfid_number' => $guruData['rfid_tag'] ?? null,
                    'is_active' => ($guruData['status'] ?? 'aktif') === 'aktif',
                ];
                
                if ($existingGuru) {
                    $existingGuru->update($userData);
                    $updatedCount++;
                } else {
                    User::create($userData);
                    $syncedCount++;
                }
            }
            
            echo "✅ Guru sync complete: {$syncedCount} new, {$updatedCount} updated\n";
        }
        
        echo "\n=== Sync completed successfully! ===\n";
        
    } catch (Exception $e) {
        echo "\n❌ Sync failed: " . $e->getMessage() . "\n";
        Log::error("Sync failed: " . $e->getMessage());
    }
}

// Jalankan test
echo "Untuk menjalankan test, gunakan:\n";
echo "testSimpelsIntegration();\n";
echo "syncDataFromSimpels();\n\n";