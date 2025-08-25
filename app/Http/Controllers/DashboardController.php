<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentTypeResource;
use App\Models\PaymentType;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        try {
            $user = auth()->user();
            $stats = $this->paymentService->getDashboardStats($user);

            // Récupérer les derniers paiements
            $recentPayments = $user->payments()
                ->with('paymentType')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Récupérer les types de paiements actifs
            $paymentTypes = PaymentType::active()->get();

            return $this->success([
                'stats' => $stats,
                'recent_payments' => PaymentResource::collection($recentPayments),
                'payment_types' => PaymentTypeResource::collection($paymentTypes),
            ]);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la récupération du tableau de bord');
        }
    }

    public function monthlyStats(Request $request)
    {
        try {
            $year = $request->get('year', now()->year);
            $user = auth()->user();
            
            $monthlyStats = [];
            
            for ($month = 1; $month <= 12; $month++) {
                $totalAmount = $user->payments()
                    ->byStatus('completed')
                    ->byMonth($month, $year)
                    ->sum('amount');
                
                $totalCount = $user->payments()
                    ->byMonth($month, $year)
                    ->count();

                $monthlyStats[] = [
                    'month' => $month,
                    'month_name' => now()->month($month)->format('F'),
                    'total_amount' => $totalAmount,
                    'total_count' => $totalCount,
                ];
            }

            return $this->success([
                'year' => $year,
                'monthly_stats' => $monthlyStats,
            ]);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la récupération des statistiques mensuelles');
        }
    }

    public function paymentTypeStats()
    {
        try {
            $user = auth()->user();
            
            $typeStats = PaymentType::withCount(['payments' => function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'completed');
            }])
            ->with(['payments' => function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'completed');
            }])
            ->get()
            ->map(function ($type) {
                $totalAmount = $type->payments->sum('amount');
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'icon' => $type->icon,
                    'total_payments' => $type->payments_count,
                    'total_amount' => $totalAmount,
                ];
            });

            return $this->success([
                'payment_type_stats' => $typeStats,
            ]);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la récupération des statistiques par type');
        }
    }
}