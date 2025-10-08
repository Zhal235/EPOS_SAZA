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
     * Test connection to SIMPels API
     */
    public function testConnection()
    {
        return $this->makeRequest('GET', $this->endpoints['limit_summary']);
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
     * Get santri by RFID tag
     */
    public function getSantriByRfid($rfidTag)
    {
        $response = $this->makeRequest('GET', $this->endpoints['santri_rfid'] . '/' . $rfidTag);
        
        // Add daily spending limit calculation
        if ($response && isset($response['success']) && $response['success'] && isset($response['data'])) {
            $santri = $response['data'];
            
            // Get today's spending to calculate remaining limit
            try {
                $dailySpending = $this->getSantriDailySpending($rfidTag);
                $todaySpent = 0;
                
                if ($dailySpending && isset($dailySpending['data']['total_spent_today'])) {
                    $todaySpent = $dailySpending['data']['total_spent_today'];
                }
                
                $dailyLimit = $santri['limit_harian'] ?? 50000;
                $remainingLimit = max(0, $dailyLimit - $todaySpent);
                
                $response['data']['limit_harian'] = $dailyLimit;
                $response['data']['spent_today'] = $todaySpent;
                $response['data']['sisa_limit_hari_ini'] = $remainingLimit;
                
            } catch (\Exception $e) {
                Log::warning('Failed to get daily spending limit for santri: ' . $e->getMessage());
                // Set default values if API call fails
                $response['data']['limit_harian'] = $santri['limit_harian'] ?? 50000;
                $response['data']['spent_today'] = 0;
                $response['data']['sisa_limit_hari_ini'] = $santri['limit_harian'] ?? 50000;
            }
        }
        
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
     * Process payment and deduct balance from santri account
     */
    public function processPayment($paymentData)
    {
        // Step 1: Deduct saldo from santri account
        $deductEndpoint = '/santri/' . $paymentData['santri_id'] . '/deduct';
        
        $deductData = [
            'nominal' => $paymentData['amount'],
            'keterangan' => $paymentData['description'] ?? 'EPOS Transaction',
            'transaction_ref' => $paymentData['transaction_ref']
        ];

        Log::info("Deducting saldo through SIMPels API", $deductData);
        
        $deductResult = $this->makeRequest('POST', $deductEndpoint, $deductData);
        
        if (!$deductResult || !isset($deductResult['success']) || !$deductResult['success']) {
            throw new \Exception('Failed to deduct saldo: ' . ($deductResult['message'] ?? 'Unknown error'));
        }

        // Step 2: Sync transaction with SIMPels for record keeping
        $syncEndpoint = '/transaction/sync';
        
        $syncData = [
            'epos_transaction_id' => $paymentData['transaction_ref'],
            'santri_id' => $paymentData['santri_id'],
            'total_amount' => $paymentData['amount'],
            'items' => $paymentData['items'] ?? [],
            'payment_method' => 'rfid',
            'transaction_date' => now()->toDateTimeString(),
            'cashier_name' => auth()->user()->name ?? 'EPOS System'
        ];

        Log::info("Syncing transaction with SIMPels", $syncData);
        
        try {
            $syncResult = $this->makeRequest('POST', $syncEndpoint, $syncData);
            Log::info("Transaction sync successful", $syncResult);
        } catch (\Exception $e) {
            // Log sync failure but don't fail the payment since saldo was already deducted
            Log::warning("Transaction sync failed but payment was successful", [
                'error' => $e->getMessage(),
                'transaction_ref' => $paymentData['transaction_ref']
            ]);
        }

        return [
            'success' => true,
            'data' => [
                'new_balance' => $deductResult['data']['saldo_sesudah'] ?? null,
                'santri_name' => $deductResult['data']['nama_santri'] ?? null,
                'transaction_id' => $paymentData['transaction_ref'],
                'saldo_deduction' => $deductResult,
                'transaction_sync' => $syncResult ?? null
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
            'timestamp' => now()->toISOString(),
            'operator' => auth()->user()->name ?? 'System'
        ];

        return $this->makeRequest('POST', $endpoint, $requestData);
    }

    /**
     * Get API health status
     */
    public function getHealthStatus()
    {
        try {
            $start = microtime(true);
            $response = $this->testConnection();
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'response_time_ms' => $duration,
                'api_url' => $this->baseUrl,
                'last_check' => now()->toDateTimeString(),
                'response' => $response
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'api_url' => $this->baseUrl,
                'last_check' => now()->toDateTimeString()
            ];
        }
    }
}