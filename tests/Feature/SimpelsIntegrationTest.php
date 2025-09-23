<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SimpelsApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class SimpelsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_connect_to_simpels_api()
    {
        Http::fake([
            '*/api/epos/limit/summary' => Http::response([
                'success' => true,
                'message' => 'Connection successful',
                'data' => []
            ], 200)
        ]);

        $service = new SimpelsApiService();
        $healthStatus = $service->getHealthStatus();

        $this->assertEquals('healthy', $healthStatus['status']);
        $this->assertArrayHasKey('response_time_ms', $healthStatus);
    }

    /** @test */
    public function it_can_fetch_all_santri_from_simpels()
    {
        $mockSantriData = [
            'success' => true,
            'message' => 'Data santri berhasil diambil',
            'data' => [
                [
                    'id' => 1,
                    'nis' => '2023001',
                    'nama_santri' => 'Ahmad Santri',
                    'email' => 'ahmad@test.com',
                    'no_hp' => '081234567890',
                    'kelas' => '7A',
                    'asrama' => 'Asrama Putra 1',
                    'rfid_tag' => 'RF001',
                    'saldo' => 50000,
                    'limit_harian' => 50000,
                    'status' => 'aktif',
                    'foto' => null
                ]
            ],
            'total' => 1
        ];

        Http::fake([
            '*/api/epos/santri/all' => Http::response($mockSantriData, 200)
        ]);

        $service = new SimpelsApiService();
        $response = $service->getAllSantri(false);

        $this->assertTrue($response['success']);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('Ahmad Santri', $response['data'][0]['nama_santri']);
    }

    /** @test */
    public function it_can_fetch_all_guru_from_simpels()
    {
        $mockGuruData = [
            'success' => true,
            'message' => 'Data guru berhasil diambil',
            'data' => [
                [
                    'id' => 1,
                    'nip' => '197001011999031001',
                    'nama_guru' => 'Dr. Budi Guru',
                    'email' => 'budi@test.com',
                    'no_hp' => '081234567891',
                    'mata_pelajaran' => 'Matematika',
                    'pengalaman_tahun' => 10,
                    'rfid_tag' => 'RF002',
                    'status' => 'aktif',
                    'foto' => null
                ]
            ],
            'total' => 1
        ];

        Http::fake([
            '*/api/epos/guru/all' => Http::response($mockGuruData, 200)
        ]);

        $service = new SimpelsApiService();
        $response = $service->getAllGuru(false);

        $this->assertTrue($response['success']);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('Dr. Budi Guru', $response['data'][0]['nama_guru']);
    }

    /** @test */
    public function it_handles_api_connection_failure()
    {
        Http::fake([
            '*/api/epos/limit/summary' => Http::response([], 500)
        ]);

        $service = new SimpelsApiService();
        $healthStatus = $service->getHealthStatus();

        $this->assertEquals('unhealthy', $healthStatus['status']);
        $this->assertArrayHasKey('error', $healthStatus);
    }

    /** @test */
    public function it_caches_santri_data()
    {
        $mockSantriData = [
            'success' => true,
            'message' => 'Data santri berhasil diambil',
            'data' => [
                [
                    'id' => 1,
                    'nis' => '2023001',
                    'nama_santri' => 'Ahmad Santri',
                    'email' => 'ahmad@test.com',
                    'no_hp' => '081234567890',
                    'kelas' => '7A',
                    'asrama' => 'Asrama Putra 1',
                    'rfid_tag' => 'RF001',
                    'saldo' => 50000,
                    'limit_harian' => 50000,
                    'status' => 'aktif',
                    'foto' => null
                ]
            ],
            'total' => 1
        ];

        Http::fake([
            '*/api/epos/santri/all' => Http::response($mockSantriData, 200)
        ]);

        $service = new SimpelsApiService();
        
        // First call should hit the API
        $response1 = $service->getAllSantri(true);
        
        // Second call should use cache
        $response2 = $service->getAllSantri(true);

        $this->assertEquals($response1, $response2);
        
        // Verify only one HTTP request was made
        Http::assertSentCount(1);
    }
}