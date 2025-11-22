<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SimpelsApiService
{
    protected $baseUrl;
    protected $timeout;
    protected $apiKey;
    protected $endpoints;

    public function __construct()
    {
        $this->baseUrl = config('services.simpels.api_url');
        $this->timeout = config('services.simpels.timeout');
        $this->apiKey = config('services.simpels.api_key');
        $this->endpoints = config('services.simpels.endpoints');
    }

    /**
     * Make HTTP request to SIMPels API
     */
    protected function makeRequest($method, $endpoint, $data = null)
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            Log::info("SIMPels API Request: {$method} {$url}", ['data' => $data]);

            $request = Http::timeout($this->timeout);

            // Add API key if available
            if ($this->apiKey) {
                $request = $request->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]);
            }

            $response = match($method) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new \Exception("Unsupported HTTP method: {$method}")
            };

            Log::info("SIMPels API Response: {$response->status()}", [
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception("API request failed with status {$response->status()}: {$response->body()}");

        } catch (\Exception $e) {
            Log::error("SIMPels API Error: " . $e->getMessage(), [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Test connection to SIMPELS API using ping endpoint
     */
    public function testConnection()
    {
        return $this->makeRequest('GET', $this->endpoints['ping']);
    }

    /**
     * Get all santri data from SIMPels
     */
    public function getAllSantri($useCache = true)
    {
        $cacheKey = 'simpels_santri_all';
        $cacheDuration = 300; // 5 minutes

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('GET', $this->endpoints['santri_all']);
        
        if ($useCache && isset($response['success']) && $response['success']) {
            Cache::put($cacheKey, $response, $cacheDuration);
        }

        return $response;
    }

    /**
     * Get all guru data from SIMPels
     */
    public function getAllGuru($useCache = true)
    {
        $cacheKey = 'simpels_guru_all';
        $cacheDuration = 300; // 5 minutes

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('GET', $this->endpoints['guru_all']);
        
        if ($useCache && isset($response['success']) && $response['success']) {
            Cache::put($cacheKey, $response, $cacheDuration);
        }

        return $response;
    }

    /**
     * Get santri by RFID tag (UID)
     */
    public function getSantriByRfid($rfidTag)
    {
        // Use new SIMPELS 2.0 endpoint: GET /api/v1/wallets/rfid/uid/{uid}
        // This endpoint already returns spent_today and sisa_limit_hari_ini calculated by backend
        $response = $this->makeRequest('GET', $this->endpoints['rfid_lookup'] . '/' . $rfidTag);
        
        return $response;
    }

    /**
     * Get guru by RFID tag
     */
    public function getGuruByRfid($rfidTag)
    {
        return $this->makeRequest('GET', $this->endpoints['guru_rfid'] . '/' . $rfidTag);
    }

    /**
     * Clear cache for all SIMPels data
     */
    public function clearCache()
    {
        Cache::forget('simpels_santri_all');
        Cache::forget('simpels_guru_all');
        
        Log::info('SIMPels API cache cleared');
    }

    /**
     * Process payment and deduct balance from santri account via SIMPELS 2.0 EPOS endpoint
     */
    public function processPayment($paymentData)
    {
        // Use SIMPELS 2.0 EPOS transaction endpoint: POST /api/v1/wallets/epos/transaction
        $eposData = [
            'santri_id' => $paymentData['santri_id'],
            'amount' => $paymentData['amount'],
            'epos_txn_id' => $paymentData['transaction_ref'],
            'meta' => [
                'items' => $paymentData['items'] ?? [],
                'description' => $paymentData['description'] ?? 'EPOS Transaction',
                'cashier' => auth()->user()->name ?? 'EPOS System',
                'terminal_id' => gethostname(),
            ]
        ];

        Log::info("Processing EPOS payment through SIMPELS 2.0 API", $eposData);
        
        $result = $this->makeRequest('POST', $this->endpoints['epos_transaction'], $eposData);
        
        if (!$result || !isset($result['success']) || !$result['success']) {
            $errorMsg = $result['message'] ?? 'Unknown error';
            Log::error('SIMPELS payment rejected', ['result' => $result]);
            throw new \Exception('Failed to process payment: ' . $errorMsg);
        }

        return [
            'success' => true,
            'data' => [
                'new_balance' => $result['data']['wallet_balance'] ?? $result['data']['balance_after'] ?? null,
                'remaining_limit' => $result['data']['remaining_limit'] ?? null,
                'santri_name' => $result['data']['santri_name'] ?? null,
                'transaction_id' => $paymentData['transaction_ref'],
                'wallet_transaction_id' => $result['data']['transaction']['id'] ?? $result['data']['transaction_id'] ?? null,
                'simpels_response' => $result
            ]
        ];
    }

    /**
     * Get transaction history for a santri
     */
    public function getSantriTransactions($rfidTag, $startDate = null, $endDate = null)
    {
        $endpoint = $this->endpoints['santri_transactions'] ?? '/epos/santri/transactions';
        
        $params = [
            'rfid_tag' => $rfidTag,
            'start_date' => $startDate ? $startDate->format('Y-m-d') : null,
            'end_date' => $endDate ? $endDate->format('Y-m-d') : null
        ];

        return $this->makeRequest('GET', $endpoint, array_filter($params));
    }

    /**
     * Get daily spending summary for santri
     */
    public function getSantriDailySpending($rfidTag, $date = null)
    {
        try {
            $endpoint = $this->endpoints['daily_spending'] ?? '/epos/santri/daily-spending';
            
            $params = [
                'rfid_tag' => $rfidTag,
                'date' => $date ? $date->format('Y-m-d') : now()->format('Y-m-d')
            ];

            return $this->makeRequest('GET', $endpoint, $params);
        } catch (\Exception $e) {
            // If endpoint doesn't exist yet, return default values
            Log::info('Daily spending endpoint not available, using defaults: ' . $e->getMessage());
            return [
                'success' => true,
                'data' => [
                    'total_spent_today' => 0,
                    'transaction_count' => 0
                ]
            ];
        }
    }

    /**
     * Top up santri balance (for admin use)
     */
    public function topUpBalance($rfidTag, $amount, $notes = null)
    {
        $endpoint = $this->endpoints['balance_topup'] ?? '/epos/balance/topup';
        
        $requestData = [
            'rfid_tag' => $rfidTag,
            'amount' => $amount,
            'notes' => $notes,
            'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            'operator' => auth()->user()->name ?? 'System'
        ];

        return $this->makeRequest('POST', $endpoint, $requestData);
    }

    /**
     * Create withdrawal request to SIMPELS 2.0
     */
    public function createWithdrawalRequest(array $withdrawalData)
    {
        try {
            // Use SIMPELS 2.0 endpoint: POST /api/v1/wallets/epos/withdrawal
            Log::info("Creating withdrawal request to SIMPELS 2.0", $withdrawalData);
            
            $response = $this->makeRequest('POST', $this->endpoints['withdrawal_create'], $withdrawalData);
            
            if ($response && isset($response['success']) && $response['success']) {
                Log::info("Withdrawal request created successfully", [
                    'withdrawal_number' => $withdrawalData['withdrawal_number'],
                    'response' => $response
                ]);
                return $response;
            }
            
            throw new \Exception('Invalid response from SIMPELS: ' . json_encode($response));

        } catch (\Exception $e) {
            Log::error('Failed to create withdrawal request to SIMPELS', [
                'error' => $e->getMessage(),
                'withdrawal_data' => $withdrawalData
            ]);
            throw $e;
        }
    }

    /**
     * Get withdrawal status from SIMPELS 2.0
     */
    public function getWithdrawalStatus($withdrawalNumber)
    {
        try {
            // Use SIMPELS 2.0 endpoint: GET /api/v1/wallets/epos/withdrawal/{withdrawalNumber}/status
            $endpoint = $this->endpoints['withdrawal_status'] . '/' . $withdrawalNumber . '/status';
            
            $response = $this->makeRequest('GET', $endpoint);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response;
            }
            
            throw new \Exception('Invalid response from SIMPELS: ' . json_encode($response));

        } catch (\Exception $e) {
            Log::error('Failed to get withdrawal status from SIMPELS', [
                'withdrawal_number' => $withdrawalNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get API health status (simplified for SIMPELS 2.0)
     */
    public function getHealthStatus()
    {
        try {
            $start = microtime(true);
            
            // Simple ping - try to access RFID endpoint
            $url = $this->baseUrl . '/rfid/uid/PING_TEST';
            $response = Http::timeout($this->timeout)->get($url);
            
            $duration = round((microtime(true) - $start) * 1000, 2);

            // 404 is OK - means API is responding
            if ($response->status() === 404 || $response->successful()) {
                return [
                    'status' => 'healthy',
                    'response_time_ms' => $duration,
                    'api_url' => $this->baseUrl,
                    'last_check' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
                    'http_status' => $response->status()
                ];
            }

            throw new \Exception("Unexpected status: {$response->status()}");

        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'api_url' => $this->baseUrl,
                'last_check' => now()->timezone('Asia/Jakarta')->toDateTimeString()
            ];
        }
    }
}