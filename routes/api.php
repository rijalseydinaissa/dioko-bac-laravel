<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// Routes protégées par JWT
Route::middleware('jwt.auth')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('monthly-stats', [DashboardController::class, 'monthlyStats']);
        Route::get('payment-type-stats', [DashboardController::class, 'paymentTypeStats']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('{payment}', [PaymentController::class, 'show']);
        Route::patch('{payment}/cancel', [PaymentController::class, 'cancel']);
        Route::patch('{payment}/approve', [PaymentController::class, 'approve']);
        Route::patch('{payment}/retry', [PaymentController::class, 'retry']);
    });

    // File routes
    Route::prefix('files')->group(function () {
        Route::get('payments/{payment}/download', [FileController::class, 'download']);
        Route::get('payments/{payment}/view', [FileController::class, 'view']);
    });
});

// Route de vérification de l'API
Route::get('health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});