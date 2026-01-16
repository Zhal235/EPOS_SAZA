<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpelsApiService;
use Illuminate\Support\Facades\Log;

class SimpelsTestController extends Controller
{
    protected $simpelsApi;

    public function __construct(SimpelsApiService $simpelsApi)
    {
        $this->simpelsApi = $simpelsApi;
    }

    /**
     * Test SIMPELS connection
     * GET /simpels/test-connection
     */
    public function testConnection()
    {
        try {
            $healthStatus = $this->simpelsApi->getHealthStatus();
            
            return response()->json([
                'success' => true,
                'message' => 'Connection test completed',
                'data' => $healthStatus,
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 500);
        }
    }

    /**
     * Test santri lookup by RFID
     * GET /simpels/test-santri/{uid}
     */
    public function testSantriLookup($uid)
    {
        try {
            Log::info("Testing SIMPELS santri lookup for UID: {$uid}");

            $result = $this->simpelsApi->getSantriByRfid($uid);
            
            if ($result && isset($result['success']) && $result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Santri found successfully',
                    'data' => [
                        'uid' => $uid,
                        'santri' => $result['data']['santri'] ?? null,
                        'wallet' => $result['data']['wallet'] ?? null,
                        'rfid_info' => $result['data']['rfid'] ?? null
                    ],
                    'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Santri not found',
                'uid' => $uid,
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 404);

        } catch (\Exception $e) {
            Log::error("SIMPELS santri lookup failed for UID {$uid}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lookup failed',
                'error' => $e->getMessage(),
                'uid' => $uid,
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 500);
        }
    }

    /**
     * Test transaction processing
     * POST /simpels/test-transaction
     */
    public function testTransaction(Request $request)
    {
        try {
            $validated = $request->validate([
                'santri_id' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.product_name' => 'required_with:items|string',
                'items.*.price' => 'required_with:items|numeric',
                'items.*.qty' => 'required_with:items|integer|min:1'
            ]);

            $transactionData = [
                'santri_id' => $validated['santri_id'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? 'Test Transaction from EPOS',
                'items' => $validated['items'] ?? [
                    [
                        'product_name' => 'Test Item',
                        'price' => $validated['amount'],
                        'qty' => 1
                    ]
                ],
                'cashier' => 'EPOS Test User',
                'terminal_id' => 'EPOS-TEST-TERMINAL'
            ];

            Log::info("Testing SIMPELS transaction", $transactionData);

            $result = $this->simpelsApi->postTransaction($transactionData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction processed successfully',
                    'data' => $result['data'],
                    'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Transaction failed',
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 422);

        } catch (\Exception $e) {
            Log::error("SIMPELS test transaction failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transaction test failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 500);
        }
    }

    /**
     * Show simple test dashboard
     * GET /simpels/dashboard
     */
    public function dashboard()
    {
        return view('simpels.test-dashboard', [
            'title' => 'SIMPELS Integration Test Dashboard',
            'simpels_url' => config('services.simpels.api_url'),
            'app_name' => config('app.name')
        ]);
    }

    /**
     * Get sample santri for testing
     * GET /simpels/get-sample-santri
     */
    public function getSampleSantri()
    {
        try {
            Log::info("Getting sample santri from SIMPELS for testing");

            $result = $this->simpelsApi->makeRequest('GET', '/epos/sample-santri');
            
            return response()->json([
                'success' => true,
                'message' => 'Sample santri retrieved',
                'data' => $result,
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to get sample santri: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sample santri',
                'error' => $e->getMessage(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get all santri data from SIMPELS
     * GET /simpels/test-all-santri
     */
    public function testAllSantri()
    {
        try {
            // This uses the protected endpoint, but we can try to call it
            $result = $this->simpelsApi->getAllSantri(false); // no cache for testing

            return response()->json([
                'success' => true,
                'message' => 'All santri data retrieved',
                'data' => $result,
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get all santri data',
                'error' => $e->getMessage(),
                'note' => 'This endpoint may require authentication to SIMPELS',
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ], 500);
        }
    }
}