<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ $latestFormationUpdate?->toAtomString() ?? now()->toAtomString() }}</lastmod>
        <priority>1.0</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc>{{ url('/formations') }}</loc>
        <lastmod>{{ $latestFormationUpdate?->toAtomString() ?? now()->toAtomString() }}</lastmod>
        <priority>0.9</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc>{{ url('/nos-tarifs') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
    </url>
    @foreach ($formations as $formation)
        <url>
            <loc>{{ url('/'.$formation->slug.'/show') }}</loc>
            <lastmod>{{ $formation->updated_at->toAtomString() }}</lastmod>
            <priority>0.8</priority>
            <changefreq>monthly</changefreq>
        </url>
    @endforeach
</urlset>
