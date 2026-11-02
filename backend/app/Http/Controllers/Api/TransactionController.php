<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\NotificationService;
use App\Services\YengapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function __construct(
        private readonly YengapayService    $yengapay,
        private readonly NotificationService $notifications
    ) {}

    // ─── Initiate a payment (after QR scan) ──────────────────────────────────

    public function initiate(Request $request)
    {
        $request->validate([
            'merchant_id'   => 'required|integer|exists:merchants,id',
            'amount'        => 'required|numeric|min:100',
            'wallet_type'   => 'required|in:wave,djamo,moov,orange,mtn',
            'wallet_number' => 'required|string',
        ]);

        $merchant = Merchant::findOrFail($request->merchant_id);

        if ($merchant->kyc_status !== 'approved') {
            return response()->json(['message' => 'Ce commerçant n\'est pas encore approuvé.'], 403);
        }

        $transaction = Transaction::create([
            'reference'         => (string) Str::uuid(),
            'merchant_id'       => $merchant->id,
            'client_id'         => $request->user()->id,
            'amount'            => $request->amount,
            'wallet_type'       => $request->wallet_type,
            'wallet_number'     => $request->wallet_number,
            'status'            => 'pending',
            'commission_amount' => 0,
        ]);

        return response()->json([
            'message'       => 'Transaction initiée.',
            'transaction_id' => $transaction->id,
            'reference'     => $transaction->reference,
            'merchant_name' => $merchant->business_name,
            'amount'        => $transaction->amount,
        ], 201);
    }

    // ─── Confirm a payment (calls Yengapay) ────────────────────────────────

    public function confirm(Request $request, int $id)
    {
        $request->validate([
            'wallet_number' => 'required|string',
            'pin'           => 'required|string|size:4',
        ]);

        $user = $request->user();

        // Verify PIN
        if (! \Illuminate\Support\Facades\Hash::check($request->pin, $user->pin_hash)) {
            return response()->json(['message' => 'PIN incorrect.'], 403);
        }

        $transaction = Transaction::where('id', $id)
            ->where('client_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $result = $this->yengapay->initiatePayment(
            amount:       (float) $transaction->amount,
            walletType:   $transaction->wallet_type,
            walletNumber: $request->wallet_number,
            reference:    $transaction->reference
        );

        if (($result['status'] ?? 'failed') === 'failed') {
            $transaction->update(['status' => 'failed']);
            return response()->json(['message' => $result['message'] ?? 'Échec du paiement.'], 422);
        }

        $transaction->update([
            'payment_provider_ref' => $result['provider_ref'] ?? null,
            'status'               => 'pending',
        ]);

        // Automatiquement valider en LOCAL car le Webhook ne peut pas atteindre 127.0.0.1
        if (config('app.env') === 'local' || config('app.debug')) {
            $this->completeTransaction($transaction, $result);
            return response()->json([
                'message'   => 'Paiement simulé avec succès (Mode Local).',
                'reference' => $transaction->reference,
                'status'    => 'success'
            ]);
        }

        return response()->json([
            'message'   => 'Paiement initié. En attente de confirmation.',
            'reference' => $transaction->reference,
        ]);
    }

    // ─── Yengapay webhook ─────────────────────────────────────────────────────

    public function webhook(Request $request)
    {
        $signature = $request->header('X-Yengapay-Signature', '');
        $payload   = $request->getContent();

        if (! $this->yengapay->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Yengapay webhook: invalid signature');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->json()->all();
        $providerRef = $data['provider_ref'] ?? null;
        $newStatus   = $data['status'] ?? null;

        if (! $providerRef || ! $newStatus) {
            return response()->json(['message' => 'Données incomplètes.'], 422);
        }

        $transaction = Transaction::where('payment_provider_ref', $providerRef)->first();

        if (! $transaction) {
            Log::warning('Yengapay webhook: transaction not found', ['provider_ref' => $providerRef]);
            return response()->json(['message' => 'Transaction introuvable.'], 404);
        }

        if ($newStatus === 'success') {
            $this->completeTransaction($transaction, $data);
        } else {
            $transaction->update(['status' => $newStatus, 'metadata' => $data]);
        }

        return response()->json(['message' => 'Webhook traité.']);
    }

    /**
     * Finalize a successful transaction (commission, balance, notifications)
     */
    private function completeTransaction(Transaction $transaction, array $metadata)
    {
        DB::transaction(function () use ($transaction, $metadata) {
            if ($transaction->status === 'success') return; // Déjà traitée

            $commissionAmount = 0; // Commission retirée pour le client selon la demande

            $transaction->update([
                'status'            => 'success',
                'commission_amount' => $commissionAmount,
                'metadata'          => array_merge($transaction->metadata ?? [], $metadata),
            ]);

            // Credit merchant balance (net of commission)
            $netAmount = $transaction->amount - $commissionAmount;
            $transaction->merchant()->increment('balance', $netAmount);

            // Notify via NotificationService (in-app + SMS)
            $this->notifications->notifyTransactionSuccess($transaction->fresh());
        });
    }

    // ─── Merchant QR code ─────────────────────────────────────────────────────

    public function getMerchantInfo(Request $request, int $merchantId)
    {
        $merchant = Merchant::where('id', $merchantId)
            ->where('kyc_status', 'approved')
            ->firstOrFail();

        return response()->json([
            'merchant_id'   => $merchant->id,
            'business_name' => $merchant->business_name,
            'qr_code_url'   => $merchant->qr_code_url,
        ]);
    }

    // ─── Client Transactions ──────────────────────────────────────────────────

    public function indexClient(Request $request)
    {
        $query = Transaction::where('client_id', $request->user()->id)->with('merchant');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        $transactions->getCollection()->transform(function ($tx) {
            $data = $tx->toArray();
            $data['merchant_name'] = $tx->merchant->business_name ?? 'Commerçant Inconnu';
            return $data;
        });

        return response()->json($transactions);
    }

    public function showClient(Request $request, int $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('client_id', $request->user()->id)
            ->with('merchant')
            ->firstOrFail();

        $data = $transaction->toArray();
        $data['merchant_name'] = $transaction->merchant->business_name ?? 'Commerçant Inconnu';

        return response()->json($data);
    }

    public function downloadReceiptClient(Request $request, int $id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('client_id', $request->user()->id)
            ->with('merchant', 'client')
            ->firstOrFail();

        $html = '
            <h1>Reçu de Paiement</h1>
            <p><strong>Référence:</strong> ' . $transaction->reference . '</p>
            <p><strong>Commerçant:</strong> ' . ($transaction->merchant->business_name ?? 'Inconnu') . '</p>
            <p><strong>Montant payé:</strong> ' . ($transaction->amount + $transaction->commission_amount) . ' XOF</p>
            <p><strong>Date:</strong> ' . $transaction->created_at->format('d/m/Y H:i') . '</p>
            <p><strong>Statut:</strong> ' . strtoupper($transaction->status) . '</p>
        ';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download('receipt_' . $transaction->reference . '.pdf');
    }
}
