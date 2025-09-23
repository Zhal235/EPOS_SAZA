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
        return $this->makeRequest('GET', $this->endpoints['santri_rfid'] . '/' . $rfidTag);
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