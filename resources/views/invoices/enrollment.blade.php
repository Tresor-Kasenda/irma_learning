@php
    $currency = $enrollment->currency ?? \App\Models\Setting::get('default_currency', 'XAF');
    $isXaf = $currency === 'XAF';
    $companyName = \App\Models\Setting::get('app_name', 'IRMA Learning');
    $contactEmail = \App\Models\Setting::get('contact_email', 'contact@irmalearning.com');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $enrollment->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #bf045b;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #bf045b;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }

        .invoice-title {
            text-align: right;
            flex: 1;
        }

        .invoice-title h1 {
            font-size: 36px;
            color: #bf045b;
            margin: 0;
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-details, .client-details {
            width: 40%;
        }

        .section-title {
            font-weight: bold;
            color: #bf045b;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .details-content {
            background: #f8fafc;
            padding: 15px;
            border-radius: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table th {
            background: #bf045b;
            color: white;
            font-weight: bold;
        }

        .total-section {
            text-align: right;
            margin-top: 30px;
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .total-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
        }

        .total-amount {
            width: 120px;
            text-align: right;
            font-size: 18px;
            color: #059669;
            font-weight: bold;
        }

        .payment-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 5px;
            padding: 20px;
            margin: 30px 0;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-top: 60px;
        }

        .status-paid {
            background: #10b981;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(191, 4, 91, 0.08);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
<div class="watermark">PAYÉ</div>

<div class="invoice-container">
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-details">
                {{ \App\Models\Setting::get('company_address', 'Douala, Cameroun') }}<br>
                {{ \App\Models\Setting::get('company_phone', '') }}<br>
                Email: {{ $contactEmail }}<br>
                @if($rccm = \App\Models\Setting::get('company_rccm'))
                    RCCM: {{ $rccm }}<br>
                @endif
                @if($niu = \App\Models\Setting::get('company_niu'))
                    NIU: {{ $niu }}<br>
                @endif
            </div>
        </div>
        <div class="invoice-title">
            <h1>FACTURE</h1>
            <div class="status-paid">PAYÉE</div>
        </div>
    </div>

    <div class="invoice-meta">
        <div class="invoice-details">
            <div class="section-title">DÉTAILS DE LA FACTURE</div>
            <div class="details-content">
                <strong>Numéro:</strong> INV-{{ str_pad($enrollment->id, 6, '0', STR_PAD_LEFT) }}<br>
                <strong>Date d'émission:</strong> {{ $enrollment->payment_processed_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
                <strong>Statut:</strong> <span style="color: #059669; font-weight: bold;">Payée</span>
            </div>
        </div>

        <div class="client-details">
            <div class="section-title">FACTURER À</div>
            <div class="details-content">
                <strong>{{ $enrollment->user->name }}</strong><br>
                {{ $enrollment->user->email }}<br>
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
        <tr>
            <th>Description</th>
            <th>Quantité</th>
            <th>Prix unitaire</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <strong>{{ $enrollment->formation->title }}</strong><br>
                <small>Formation en ligne</small>
            </td>
            <td>1</td>
            <td>{{ number_format($enrollment->amount_paid, 0, ',', ' ') }} {{ $currency }}</td>
            <td>{{ number_format($enrollment->amount_paid, 0, ',', ' ') }} {{ $currency }}</td>
        </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <div class="total-label">Total à payer:</div>
            <div class="total-amount" style="font-size: 24px;">
                {{ number_format($enrollment->amount_paid, 0, ',', ' ') }} {{ $currency }}
            </div>
        </div>
    </div>

    <div class="payment-info">
        <div class="section-title">INFORMATIONS DE PAIEMENT</div>
        <strong>Méthode de paiement:</strong> {{ ucfirst($enrollment->payment_method) }}<br>
        <strong>ID de transaction:</strong> {{ $enrollment->payment_transaction_id }}<br>
        <strong>Date de paiement:</strong> {{ $enrollment->payment_processed_at?->format('d/m/Y à H:i') }}<br>
        <strong>Statut:</strong> <span style="color: #059669; font-weight: bold;">Paiement confirmé</span>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <strong>Signature du client</strong><br>
            <small>(Bon pour accord)</small>
        </div>
        <div class="signature-box">
            <strong>{{ $companyName }}</strong><br>
            <small>Représentant légal</small>
        </div>
    </div>

    <div class="footer">
        <p><strong>{{ $companyName }}</strong> — {{ \App\Models\Setting::get('company_address', 'Douala, Cameroun') }}</p>
        <p>Contact : {{ $contactEmail }}</p>
        <hr style="margin: 20px 0;">
        <p>Merci pour votre confiance !</p>
    </div>
</div>
</body>
</html>
