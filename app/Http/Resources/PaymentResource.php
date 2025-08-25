<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_reference' => $this->payment_reference,
            'external_reference' => $this->external_reference,
            'description' => $this->description,
            'amount' => $this->amount,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'payment_type' => [
                'id' => $this->paymentType->id,
                'name' => $this->paymentType->name,
                'slug' => $this->paymentType->slug,
                'icon' => $this->paymentType->icon,
            ],
            'attachment' => $this->attachment_path ? [
                'path' => Storage::url($this->attachment_path),
                'type' => $this->attachment_type,
            ] : null,
            'payment_details' => $this->payment_details,
            'failure_reason' => $this->failure_reason,
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }
}