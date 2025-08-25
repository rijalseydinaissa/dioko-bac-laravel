<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['balance' => 1000000]);
        $this->token = JWTAuth::fromUser($this->user);
        
        PaymentType::factory()->create([
            'id' => 1,
            'name' => 'Internet',
            'slug' => 'internet'
        ]);
    }

    public function test_user_can_create_payment()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('invoice.pdf', 100);

        $paymentData = [
            'payment_type_id' => 1,
            'description' => 'Internet mois d\'août 2025',
            'amount' => 25000,
            'attachment' => $file,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', $paymentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'payment_reference',
                        'description',
                        'amount',
                        'status',
                    ]
                ]);
    }

    public function test_user_cannot_create_payment_with_insufficient_balance()
    {
        $this->user->update(['balance' => 1000]);

        $paymentData = [
            'payment_type_id' => 1,
            'description' => 'Gros paiement',
            'amount' => 2000,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', $paymentData);

        $response->assertStatus(400);
    }

    public function test_user_can_get_payment_history()
    {
        Payment::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payments');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'payments',
                        'total',
                        'filters'
                    ]
                ]);
    }

    public function test_user_can_cancel_pending_payment()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/payments/{$payment->id}/cancel");

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'cancelled'
        ]);
    }
}