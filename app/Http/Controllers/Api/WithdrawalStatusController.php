<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SimpelsWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WithdrawalStatusController extends Controller
{
    /**
     * Update withdrawal status from SIMPels
     */
    public function updateStatus(Request $request, $withdrawalNumber)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,approved,rejected,completed',
                'updated_by' => 'nullable|string',
                'notes' => 'nullable|string',
                'updated_at' => 'nullable|date',
            ]);

            $withdrawal = SimpelsWithdrawal::where('withdrawal_number', $withdrawalNumber)->first();

            if (!$withdrawal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }

            // Update withdrawal status
            $updateData = [
                'simpels_status' => $request->status,
                'simpels_updated_at' => $request->updated_at ? now()->parse($request->updated_at) : now(),
            ];

            if ($request->notes) {
                $updateData['simpels_notes'] = $request->notes;
            }

            $withdrawal->update($updateData);

            Log::info('Withdrawal status updated from SIMPels', [
                'withdrawal_number' => $withdrawalNumber,
                'old_status' => $withdrawal->status,
                'new_status' => $request->status,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'withdrawal_number' => $withdrawal->withdrawal_number,
                    'status' => $withdrawal->simpels_status,
                    'updated_at' => $withdrawal->simpels_updated_at
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update withdrawal status', [
                'withdrawal_number' => $withdrawalNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal details
     */
    public function getWithdrawal($withdrawalNumber)
    {
        try {
            $withdrawal = SimpelsWithdrawal::where('withdrawal_number', $withdrawalNumber)->first();

            if (!$withdrawal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawal_number' => $withdrawal->withdrawal_number,
                    'status' => $withdrawal->status,
                    'simpels_status' => $withdrawal->simpels_status,
                    'total_amount' => $withdrawal->total_amount,
                    'withdrawal_method' => $withdrawal->withdrawal_method,
                    'created_at' => $withdrawal->created_at,
                    'simpels_updated_at' => $withdrawal->simpels_updated_at,
                    'simpels_notes' => $withdrawal->simpels_notes,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get withdrawal details', [
                'withdrawal_number' => $withdrawalNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }
}