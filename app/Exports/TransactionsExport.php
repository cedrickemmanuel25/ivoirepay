<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Commerçant',
            'Client',
            'Montant (XOF)',
            'Wallet',
            'Statut',
            'Commission (XOF)',
            'Date'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->reference,
            $transaction->merchant->business_name ?? 'Inconnu',
            $transaction->client->name ?? 'Inconnu',
            $transaction->amount,
            ucfirst($transaction->wallet_type),
            ucfirst($transaction->status),
            $transaction->commission_amount,
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
