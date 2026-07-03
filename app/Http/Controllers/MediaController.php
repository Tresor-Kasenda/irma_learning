<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EnrollmentPaymentEnum;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MediaController extends Controller
{
    public function stream(Chapter $chapter, string $type, Request $request): StreamedResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $formation = $chapter->section->formation;

        $enrollment = $formation->enrollments()
            ->where('user_id', $user->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->first();

        if (! $enrollment && ! $user->isAdmin()) {
            abort(403);
        }

        $path = $type === 'video' ? $chapter->video_url : $chapter->media_url;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $mimeTypes = [
            'mp4' => 'video/mp4',
            'pdf' => 'application/pdf',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
        ];

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mime = $mimeTypes[$extension] ?? 'application/octet-stream';

        return Storage::disk('public')->response($path, null, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
