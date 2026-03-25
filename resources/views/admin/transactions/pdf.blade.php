<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h2 {
            text-align: center;
            color: #1B4332;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Historique des Transactions IvoirePay</h2>
    <p>Export généré le : {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Commerçant</th>
                <th>Client</th>
                <th class="text-right">Montant (F)</th>
                <th class="text-center">Wallet</th>
                <th class="text-center">Statut</th>
                <th class="text-right">Comm. (F)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
                <tr>
                    <td>{{ substr($tx->reference, 0, 15) }}...</td>
                    <td>{{ $tx->merchant->business_name ?? 'N/A' }}</td>
                    <td>{{ $tx->client->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($tx->amount, 0, ',', ' ') }}</td>
                    <td class="text-center">{{ ucfirst($tx->wallet_type) }}</td>
                    <td class="text-center">{{ ucfirst($tx->status) }}</td>
                    <td class="text-right">{{ number_format($tx->commission_amount, 0, ',', ' ') }}</td>
                    <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
