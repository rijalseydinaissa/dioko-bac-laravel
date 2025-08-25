<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['status', 'date_from', 'date_to', 'month', 'year']);
            $payments = $this->paymentService->getPaymentHistory(auth()->user(), $filters);

            return $this->success([
                'payments' => PaymentResource::collection($payments),
                'total' => $payments->count(),
                'filters' => $filters,
            ]);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la récupération des paiements');
        }
    }

    public function store(PaymentRequest $request)
    {
        try {
            $user = auth()->user();
            
            // Créer le paiement
            $payment = $this->paymentService->createPayment(
                $user,
                $request->validated(),
                $request->file('attachment')
            );

            // Traiter le paiement immédiatement
            $this->paymentService->processPayment($payment);

            return $this->success(
                new PaymentResource($payment->fresh('paymentType')),
                'Paiement créé et traité avec succès',
                201
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function show(Payment $payment)
    {
        try {
            // Vérifier que le paiement appartient à l'utilisateur connecté
            if ($payment->user_id !== auth()->id()) {
                return $this->forbidden('Accès interdit à ce paiement');
            }

            return $this->success(new PaymentResource($payment->load('paymentType')));

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la récupération du paiement');
        }
    }

    public function cancel(Payment $payment)
    {
        try {
            // Vérifier que le paiement appartient à l'utilisateur connecté
            if ($payment->user_id !== auth()->id()) {
                return $this->forbidden('Accès interdit à ce paiement');
            }

            if (!$payment->isPending() && !$payment->isProcessing()) {
                return $this->error('Ce paiement ne peut pas être annulé');
            }

            // Rembourser le solde si le paiement était en cours
            if ($payment->isProcessing()) {
                $payment->user->addBalance($payment->amount);
            }

            $payment->cancel();

            return $this->success(
                new PaymentResource($payment->fresh('paymentType')),
                'Paiement annulé avec succès'
            );

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de l\'annulation du paiement');
        }
    }

    public function retry(Payment $payment)
    {
        try {
            // Vérifier que le paiement appartient à l'utilisateur connecté
            if ($payment->user_id !== auth()->id()) {
                return $this->forbidden('Accès interdit à ce paiement');
            }

            if (!$payment->isFailed()) {
                return $this->error('Seuls les paiements échoués peuvent être retentés');
            }

            // Réinitialiser le statut
            $payment->update([
                'status' => 'pending',
                'failure_reason' => null,
                'processed_at' => null,
            ]);

            // Retraiter le paiement
            $this->paymentService->processPayment($payment);

            return $this->success(
                new PaymentResource($payment->fresh('paymentType')),
                'Paiement retenté avec succès'
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}