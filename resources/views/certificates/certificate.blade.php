@php
    $branding = \App\Models\ApplicationSetting::current();
    $companyName = $branding->app_name ?: \App\Models\Setting::get('app_name', 'IRMA Learning');
    $companyTagline = $branding->app_tagline ?: 'Plateforme de formation professionnelle';
    $signatureName = $branding->certificate_signature_name ?: $companyName;
    $verificationUrl = $certificate->verification_url;
    $logoPath = $branding->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($branding->logo_path)
        ? \Illuminate\Support\Facades\Storage::disk('public')->path($branding->logo_path)
        : public_path('images/irma-logo-base.svg');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat - {{ $certificate->certificate_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f0e8;
        }
        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .border-frame {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 3px solid #caa45a;
            pointer-events: none;
        }
        .inner-border {
            position: absolute;
            top: 26px;
            left: 26px;
            right: 26px;
            bottom: 26px;
            border: 1px solid #caa45a;
            pointer-events: none;
        }
        .content {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            text-align: center;
        }
        .logo {
            height: 60px;
            margin-bottom: 20px;
        }
        .label {
            font-size: 14px;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: #caa45a;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .awarded-to {
            font-size: 13px;
            color: #888;
            margin-bottom: 5px;
        }
        .certificate-subtitle {
            font-size: 12px;
            color: #7c7c7c;
            margin-bottom: 10px;
        }
        .holder-name {
            font-size: 36px;
            font-weight: 700;
            color: #1a2a3a;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .completion-text {
            font-size: 13px;
            color: #888;
            margin-bottom: 5px;
        }
        .formation-title {
            font-size: 24px;
            font-weight: 600;
            color: #bf045b;
            margin-bottom: 25px;
        }
        .details {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }
        .detail-box {
            border: 1px solid #ddd;
            padding: 10px 20px;
            min-width: 120px;
        }
        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #1a2a3a;
            margin-top: 3px;
        }
        .issuer {
            font-size: 13px;
            font-weight: 600;
            color: #1a2a3a;
        }
        .issuer-subtitle {
            font-size: 11px;
            color: #888;
            margin-top: 4px;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #aaa;
        }
        .footer a {
            color: #caa45a;
            text-decoration: none;
        }
        .verification-hash {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
            color: #bbb;
            margin-top: 5px;
        }
        .verification-url {
            font-size: 10px;
            color: #caa45a;
            margin-top: 6px;
            word-break: break-all;
        }
    </style>
</head>
<body>
<div class="certificate">
    <div class="border-frame"></div>
    <div class="inner-border"></div>

    <div class="content">
        <img class="logo" src="{{ $logoPath }}" alt="IRMA">

        <div class="label">Certificat de réussite</div>
        <div class="certificate-subtitle">{{ $companyTagline }}</div>
        <div class="awarded-to">Décerné à</div>
        <div class="holder-name">{{ $certificate->user->name }}</div>
        <div class="completion-text">pour avoir complété avec succès la formation</div>
        <div class="formation-title">{{ $certificate->formation->title }}</div>

        <div class="details">
            <div class="detail-box">
                <div class="detail-label">Score</div>
                <div class="detail-value">{{ round($certificate->final_score ?? 0) }}%</div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Délivré le</div>
                <div class="detail-value">{{ $certificate->issue_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="issuer">{{ $signatureName }}</div>
        <div class="issuer-subtitle">{{ $companyName }}</div>
    </div>

    <div class="footer">
        <p>{{ $companyName }}</p>
        <p>N° {{ $certificate->certificate_number }}</p>
        <div class="verification-hash">{{ $certificate->verification_hash }}</div>
        <div class="verification-url">{{ $verificationUrl }}</div>
    </div>
</div>
</body>
</html>
