<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function __construct(
        private readonly QrCodeService      $qrService,
        private readonly NotificationService $notifications
    ) {}

    // ─── Submit KYC dossier ──────────────────────────────────────────────────

    public function submit(Request $request)
    {
        $request->validate([
            'business_name'    => 'required|string|max:150',
            'business_address' => 'required|string|max:255',
            'rccm_number'      => 'nullable|string|max:50',
            'cni_number'       => 'required|string|max:50',
            'documents'        => 'required|array|min:1',
            'documents.*'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'document_types'   => 'required|array|min:1',
            'document_types.*' => 'required|in:cni,rccm,business_permit,other',
        ]);

        $user = $request->user();

        // Create or update merchant record
        $merchant = Merchant::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name'    => $request->business_name,
                'business_address' => $request->business_address,
                'rccm_number'      => $request->rccm_number,
                'cni_number'       => $request->cni_number,
                'kyc_status'       => 'pending',
                'kyc_rejection_reason' => null,
            ]
        );

        // Store uploaded documents
        foreach ($request->file('documents') as $index => $file) {
            $type         = $request->document_types[$index] ?? 'other';
            $originalName = $file->getClientOriginalName();
            $path         = $file->store("public/kyc/{$merchant->id}");

            KycDocument::create([
                'merchant_id'   => $merchant->id,
                'document_type' => $type,
                'file_path'     => $path,
                'original_name' => $originalName,
            ]);
        }

        // Generate static QR code if not yet generated
        if (! $merchant->qr_code_path) {
            $this->qrService->generateForMerchant($merchant);
        }

        // Notify admins via NotificationService
        $this->notifications->notifyAdminsNewKyc($merchant);

        return response()->json([
            'status'  => 'pending',
            'message' => 'Dossier en cours de traitement.',
        ]);
    }

    // ─── Get KYC Status ──────────────────────────────────────────────────────

    public function status(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['status' => null, 'message' => 'Aucun dossier KYC soumis.'], 404);
        }

        return response()->json([
            'kyc_status'             => $merchant->kyc_status,
            'kyc_rejection_reason'   => $merchant->kyc_rejection_reason,
            'approved_at'            => $merchant->approved_at,
            'has_pin'                => (bool)$request->user()->has_pin,
        ]);
    }

    // ─── Upload a single document ────────────────────────────────────────────

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'document_type' => 'required|in:cni,rccm,business_permit,other',
        ]);

        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['message' => 'Créez d\'abord votre profil commerçant.'], 422);
        }

        $file = $request->file('document');
        $path = $file->store("public/kyc/{$merchant->id}");

        $doc = KycDocument::create([
            'merchant_id'   => $merchant->id,
            'document_type' => $request->document_type,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return response()->json([
            'message'       => 'Document uploadé.',
            'document_id'   => $doc->id,
            'document_type' => $doc->document_type,
            'file_url'      => Storage::url($path),
        ], 201);
    }
}
