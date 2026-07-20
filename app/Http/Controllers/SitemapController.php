<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Formation;

final class SitemapController extends Controller
{
    public function __invoke(): \Illuminate\Http\Response
    {
        $formations = Formation::query()->active()->get(['slug', 'updated_at']);

        $content = view('sitemap', ['formations' => $formations])->render();

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }
}
