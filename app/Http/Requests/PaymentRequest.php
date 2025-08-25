<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type_id' => ['required', 'integer', 'exists:payment_types,id'],
            'description' => ['required', 'string', 'max:500', 'min:3'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'payment_type_id.required' => 'Le type de paiement est obligatoire.',
            'payment_type_id.exists' => 'Le type de paiement sélectionné n\'existe pas.',
            'description.required' => 'La description est obligatoire.',
            'description.min' => 'La description doit contenir au moins 3 caractères.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
            'amount.max' => 'Le montant ne peut pas dépasser 999999.99.',
            'attachment.file' => 'Le fichier joint doit être un fichier valide.',
            'attachment.mimes' => 'Le fichier doit être au format PDF, JPG, JPEG ou PNG.',
            'attachment.max' => 'Le fichier ne peut pas dépasser 5 Mo.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}