<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PaymentService
{
    private MockPaymentAPIService $paymentAPI;

    public function __construct(MockPaymentAPIService $paymentAPI)
    {
        $this->paymentAPI = $paymentAPI;
    }

    public function createPayment(User $user, array $paymentData, ?UploadedFile $attachment = null): Payment
    {
        return DB::transaction(function () use ($user, $paymentData, $attachment) {
            // Vérifier le solde disponible
            if (!$user->hasEnoughBalance($paymentData['amount'])) {
                throw new \Exception('Solde insuffisant pour effectuer ce paiement');
            }

            // Gérer l'upload du fichier
            $attachmentPath = null;
            $attachmentType = null;
            
            if ($attachment) {
                $attachmentPath = $attachment->store('payments', 'public');
                $attachmentType = $attachment->getClientOriginalExtension();
            }

            // Créer le paiement
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type_id' => $paymentData['payment_type_id'],
                'description' => $paymentData['description'],
                'amount' => $paymentData['amount'],
                'status' => 'pending',
                'attachment_path' => $attachmentPath,
                'attachment_type' => $attachmentType,
            ]);

            return $payment;
        });
    }

    public function processPayment(Payment $payment): bool
    {
        if (!$payment->isPending()) {
            throw new \Exception('Ce paiement ne peut pas être traité');
        }

        $payment->markAsProcessing();

        try {
            // Déduire le montant du solde utilisateur
            $payment->user->deductBalance($payment->amount);

            // Appeler l'API de paiement fictive
            $apiResponse = $this->paymentAPI->processPayment([
                'payment_reference' => $payment->payment_reference,
                'amount' => $payment->amount,
                'description' => $payment->description,
                'user_id' => $payment->user_id,
            ]);

            if ($apiResponse['success']) {
                $payment->update([
                    'status' => 'completed',
                    'external_reference' => $apiResponse['external_reference'],
                    'payment_details' => $apiResponse,
                    'processed_at' => now(),
                ]);

                return true;
            } else {
                // Rembourser le solde en cas d'échec
                $payment->user->addBalance($payment->amount);
                
                $payment->markAsFailed($apiResponse['error_message'] ?? 'Erreur lors du traitement');
                
                return false;
            }

        } catch (\Exception $e) {
            // Rembourser le solde en cas d'exception
            if ($payment->user->balance < $payment->amount) {
                $payment->user->addBalance($payment->amount);
            }
            
            $payment->markAsFailed($e->getMessage());
            
            throw $e;
        }
    }

    public function getPaymentHistory(User $user, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $user->payments()->with('paymentType');

        // Filtres par statut
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filtres par date
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        // Filtre par mois
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query->byMonth($filters['month'], $filters['year']);
        }

        // Filtre par année
        if (!empty($filters['year']) && empty($filters['month'])) {
            $query->byYear($filters['year']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getDashboardStats(User $user): array
    {
        $totalPayments = $user->payments()->count();
        $completedPayments = $user->payments()->byStatus('completed')->count();
        $pendingPayments = $user->payments()->byStatus('pending')->count();
        $failedPayments = $user->payments()->byStatus('failed')->count();
        
        $totalAmountSpent = $user->payments()
            ->byStatus('completed')
            ->sum('amount');

        $thisMonthPayments = $user->payments()
            ->byMonth(now()->month, now()->year)
            ->count();

        $thisMonthAmount = $user->payments()
            ->byMonth(now()->month, now()->year)
            ->byStatus('completed')
            ->sum('amount');

        return [
            'balance' => $user->balance,
            'total_payments' => $totalPayments,
            'completed_payments' => $completedPayments,
            'pending_payments' => $pendingPayments,
            'failed_payments' => $failedPayments,
            'total_amount_spent' => $totalAmountSpent,
            'this_month_payments' => $thisMonthPayments,
            'this_month_amount' => $thisMonthAmount,
            'success_rate' => $totalPayments > 0 ? ($completedPayments / $totalPayments) * 100 : 0,
        ];
    }
}