<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateAvatarRequest;
use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Student/Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'formations' => Enrollment::query()
                ->where('user_id', $user->id)
                ->with('formation:id,title,slug,short_description,image')
                ->latest('last_accessed_at')
                ->get()
                ->map(fn (Enrollment $enrollment): array => [
                    'id' => $enrollment->formation_id,
                    'title' => $enrollment->formation?->title,
                    'slug' => $enrollment->formation?->slug,
                    'short_description' => $enrollment->formation?->short_description,
                    'image' => $enrollment->formation?->image,
                    'progress' => (float) $enrollment->progress_percentage,
                    'status' => $enrollment->status->getLabel(),
                    'last_accessed_at' => $enrollment->last_accessed_at?->toISOString(),
                ]),
            'certificates' => Certificate::query()
                ->where('user_id', $user->id)
                ->with('formation:id,title,slug')
                ->latest('issue_date')
                ->get()
                ->map(fn (Certificate $certificate): array => [
                    'id' => $certificate->id,
                    'number' => $certificate->certificate_number,
                    'formation_title' => $certificate->formation?->title,
                    'score' => (float) $certificate->final_score,
                    'status' => $certificate->status->getLabel(),
                    'issue_date' => $certificate->issue_date?->toISOString(),
                ]),
        ]);
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        $previousAvatar = $user->avatar;

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        if ($previousAvatar !== null && Storage::disk('public')->exists($previousAvatar)) {
            Storage::disk('public')->delete($previousAvatar);
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
