<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class FileController extends Controller
{
    use ApiResponse;

    public function download(Payment $payment)
    {
        try {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            // Vérifier que le paiement appartient à l'utilisateur connecté
            if ($payment->user_id !== auth()->id()) {
                return $this->forbidden('Accès interdit à ce fichier');
            }

            // Vérifier que le fichier existe
            if (!$payment->attachment_path || !$disk->exists($payment->attachment_path)) {
                return $this->notFound('Fichier non trouvé');
            }

            // Télécharger le fichier
            return $disk->download($payment->attachment_path);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors du téléchargement du fichier');
        }
    }

    public function view(Payment $payment)
    {
        try {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            // Vérifier que le paiement appartient à l'utilisateur connecté
            if ($payment->user_id !== auth()->id()) {
                return $this->forbidden('Accès interdit à ce fichier');
            }

            // Vérifier que le fichier existe
            if (!$payment->attachment_path || !$disk->exists($payment->attachment_path)) {
                return $this->notFound('Fichier non trouvé');
            }

            // Lire le fichier et détecter le type MIME
            $file = $disk->get($payment->attachment_path);
            $mimeType = $disk->mimeType($payment->attachment_path);

            return response($file, 200)->header('Content-Type', $mimeType);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de l\'affichage du fichier');
        }
    }
}
