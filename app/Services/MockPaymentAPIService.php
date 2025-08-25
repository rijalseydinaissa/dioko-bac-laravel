<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MockPaymentAPIService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('app.mock_payment_api_url', 'http://localhost:8001/api');
        $this->apiKey = config('app.mock_payment_api_key', 'mock-api-key-123');
    }

    public function processPayment(array $paymentData): array
    {
        // Simulation d'un appel API externe
        // En réalité, ceci ferait un appel HTTP à une vraie API de paiement
        
        // Simuler des réponses différentes selon le montant
        $amount = $paymentData['amount'];
        $success = $amount <= 500000; // Les paiements > 500,000 échouent pour la démo
        
        // Simuler un délai de traitement
        usleep(rand(500000, 2000000)); // 0.5 à 2 secondes

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                'external_reference' => 'EXT-' . strtoupper(Str::random(8)),
                'status' => 'completed',
                'processed_at' => now()->toISOString(),
                'fees' => $amount * 0.025, // 2.5% de frais
                'net_amount' => $amount - ($amount * 0.025),
            ];
        } else {
            return [
                'success' => false,
                'error_code' => 'INSUFFICIENT_FUNDS',
                'error_message' => 'Fonds insuffisants pour effectuer cette transaction',
                'transaction_id' => null,
            ];
        }
    }

    public function checkTransactionStatus(string $transactionId): array
    {
        // Simulation de vérification du statut d'une transaction
        return [
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'processed_at' => now()->subMinutes(5)->toISOString(),
        ];
    }
}