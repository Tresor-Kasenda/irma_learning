<?php
/** @var Enrollment $enrollment */
?>
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $enrollment->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }

        .invoice-title {
            text-align: right;
            flex: 1;
        }

        .invoice-title h1 {
            font-size: 36px;
            color: #2563eb;
            margin: 0;
        }

        .invoice-meta {
            display: flex;
            justify-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-details, .client-details {
            width: 40%;
        }

        .section-title {
            font-weight: bold;
            color: #2563eb;
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
            background: #2563eb;
            color: white;
            font-weight: bold;
        }

        .items-table tr:hover {
            background: #f8fafc;
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
            width: 100px;
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
            font-size: 12px;
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
            color: rgba(37, 99, 235, 0.1);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
<div class="watermark">PAYÉ</div>

<div class="invoice-container">
    <!-- En-tête -->
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ config('app.name', 'Formation Academy') }}</div>
            <div class="company-details">
                123 Rue de la Formation<br>
                75001 Paris, France<br>
                Tél: +33 1 23 45 67 89<br>
                Email: contact@formation-academy.fr<br>
                SIRET: 123 456 789 00010<br>
                TVA: FR12345678901
            </div>
        </div>
        <div class="invoice-title">
            <h1>FACTURE</h1>
            <div class="status-paid">PAYÉE</div>
        </div>
    </div>

    <!-- Informations facture et client -->
    <div class="invoice-meta">
        <div class="invoice-details">
            <div class="section-title">DÉTAILS DE LA FACTURE</div>
            <div class="details-content">
                <strong>Numéro:</strong> FA-{{ str_pad($enrollment->id, 6, '0', STR_PAD_LEFT) }}<br>
                <strong>Date
                    d'émission:</strong> {{ $enrollment->payment_processed_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                <br>
                <strong>Date
                    d'échéance:</strong> {{ $enrollment->payment_processed_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                <br>
                <strong>Statut:</strong> <span style="color: #059669; font-weight: bold;">Payée</span>
            </div>
        </div>

        <div class="client-details">
            <div class="section-title">FACTURER À</div>
            <div class="details-content">
                <strong>{{ $enrollment->user->name }}</strong><br>
                {{ $enrollment->user->email }}<br>
                {{--                @if($enrollment->user->address)--}}
                {{--                    {{ $enrollment->user->address }}<br>--}}
                {{--                @endif--}}
                {{--                @if($enrollment->user->city && $enrollment->user->postal_code)--}}
                {{--                    {{ $enrollment->user->postal_code }} {{ $enrollment->user->city }}<br>--}}
                {{--                @endif--}}
                {{--                @if($enrollment->user->phone)--}}
                {{--                    Tél: {{ $enrollment->user->phone }}--}}
                {{--                @endif--}}
            </div>
        </div>
    </div>

    <!-- Tableau des articles -->
    <table class="items-table">
        <thead>
        <tr>
            <th>Description</th>
            <th>Quantité</th>
            <th>Prix unitaire</th>
            <th>Total HT</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <strong>{{ $enrollment->formation->title }}</strong><br>
                <small>Formation en ligne - Accès à vie</small><br>
                <small>Période: {{ $enrollment->created_at->format('d/m/Y') }}
                    - {{ $enrollment->created_at->addMonths(12)->format('d/m/Y') }}</small>
            </td>
            <td>1</td>
            <td>€{{ number_format($enrollment->amount_paid / 1.2, 2, ',', ' ') }}</td>
            <td>€{{ number_format($enrollment->amount_paid / 1.2, 2, ',', ' ') }}</td>
        </tr>
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="total-section">
        <div class="total-row">
            <div class="total-label">Sous-total HT:</div>
            <div class="total-amount">€{{ number_format($enrollment->amount_paid / 1.2, 2, ',', ' ') }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">TVA (20%):</div>
            <div class="total-amount">
                €{{ number_format($enrollment->amount_paid - ($enrollment->amount_paid / 1.2), 2, ',', ' ') }}</div>
        </div>
        <div class="total-row" style="border-top: 2px solid #2563eb; padding-top: 10px;">
            <div class="total-label">TOTAL TTC:</div>
            <div class="total-amount" style="font-size: 24px;">
                €{{ number_format($enrollment->amount_paid, 2, ',', ' ') }}</div>
        </div>
    </div>

    <!-- Informations de paiement -->
    <div class="payment-info">
        <div class="section-title">INFORMATIONS DE PAIEMENT</div>
        <strong>Méthode de paiement:</strong> {{ ucfirst($enrollment->payment_method) }}<br>
        <strong>ID de transaction:</strong> {{ $enrollment->payment_transaction_id }}<br>
        <strong>Date de paiement:</strong> {{ $enrollment->payment_processed_at?->format('d/m/Y à H:i') }}<br>
        <strong>Statut:</strong> <span style="color: #059669; font-weight: bold;">Paiement confirmé</span>
    </div>

    <!-- Section signature -->
    <div class="signature-section">
        <div class="signature-box">
            <strong>Signature du client</strong><br>
            <small>(Bon pour accord)</small>
        </div>
        <div class="signature-box">
            <strong>Formation Academy</strong><br>
            <small>Représentant légal</small>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>Conditions de paiement:</strong> Paiement immédiat par carte bancaire ou PayPal</p>
        <p><strong>Mentions légales:</strong> Formation Academy - SARL au capital de 10 000€ - RCS Paris 123 456 789</p>
        <p>En cas de retard de paiement, des pénalités au taux de 3 fois le taux d'intérêt légal seront applicables.</p>
        <hr style="margin: 20px 0;">
        <p>Merci pour votre confiance ! Pour toute question : contact@formation-academy.fr</p>
    </div>
</div>
</body>
</html>
