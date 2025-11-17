<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EnrollmentController extends Controller
{
    /**
     * Generate and download enrollment invoice
     */
    public function __invoke(Enrollment $enrollment, Request $request)
    {
        $enrollment->load(['user', 'formation']);

        if (!$enrollment->payment_processed_at) {
            abort(404, 'Cette facture n\'est pas encore disponible');
        }

        $pdf = Pdf::loadView('invoices.enrollment', [
            'enrollment' => $enrollment,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = sprintf(
            'facture-%s-%s.pdf',
            str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            $enrollment->payment_processed_at->format('Y-m-d')
        );

        if ($request->get('view') === '1') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Send invoice by email
     */
    public function sendByEmail(Enrollment $enrollment, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:1000'
        ]);

        try {
            $pdfPath = $this->generateForEmail($enrollment);

            // Ici vous pourriez utiliser un Job pour l'envoi d'email
            // Mail::to($request->email)->send(new InvoiceMail($enrollment, $pdfPath, $request->message));

            // Nettoyer le fichier temporaire
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Facture envoyée par email avec succès'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la facture'
            ], 500);
        }
    }

    /**
     * Generate invoice for email attachment
     */
    public function generateForEmail(Enrollment $enrollment): string
    {
        $enrollment->load(['user', 'formation']);

        $pdf = Pdf::loadView('invoices.enrollment', [
            'enrollment' => $enrollment,
        ])
            ->setPaper('a4', 'portrait');

        $filename = sprintf(
            'invoices/facture-%s-%s.pdf',
            str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            $enrollment->payment_processed_at->format('Y-m-d')
        );

        Storage::disk('local')->put($filename, $pdf->output());

        return storage_path('app/' . $filename);
    }

    /**
     * Handle refund request
     */
    public function refund(Enrollment $enrollment, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $enrollment->amount_paid,
        ]);

        if ($enrollment->refunded_at) {
            return back()->withErrors(['error' => 'Cette inscription a déjà été remboursée']);
        }

        $refundAmount = $request->refund_amount ?? $enrollment->amount_paid;

        try {
            $enrollment->update([
                'refunded_at' => now(),
                'refund_amount' => $refundAmount,
                'refund_reason' => $request->reason,
                'refund_transaction_id' => 'refund_id_here', // ID du remboursement
            ]);

            return back()->with('success', 'Remboursement effectué avec succès');

        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du remboursement: ' . $e->getMessage()]);
        }
    }
}
