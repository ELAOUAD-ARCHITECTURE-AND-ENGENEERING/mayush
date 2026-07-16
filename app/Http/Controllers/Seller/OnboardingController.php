<?php

namespace App\Http\Controllers\Seller;

use App\Models\SellerDocument;
use App\Models\Shop;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Utility\EmailUtility;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    /**
     * Display the document upload page.
     */
    public function index()
    {
        $shop = Auth::user()->shop;
        
        if (!$shop) {
            flash(translate('Shop not found.'))->error();
            return redirect()->route('dashboard');
        }

        // If approved, no need to be here
        if ($shop->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $documents = $shop->documents()->orderByDesc('version')->orderByDesc('id')->get()->unique('document_type')->keyBy('document_type');
        
        return view('seller.onboarding.index', compact('shop', 'documents'));
    }

    /**
     * Download the MayushSeller contract template.
     */
    public function downloadContract()
    {
        // Assuming the contract is stored in storage/app/contracts/mayush_seller_contract.pdf
        // The user mentioned "stored as pdf in this location". We will use a standard path.
        $path = storage_path('app/contracts/mayush_seller_contract.pdf');
        
        if (!file_exists($path)) {
            flash(translate('Contract file not found. Please contact support.'))->error();
            return back();
        }

        return response()->download($path);
    }

    public function downloadDocument(SellerDocument $document)
    {
        $this->authorize('view', $document);
        $path = $document->safeStoragePath();

        abort_unless($path !== null && Storage::disk('seller_documents')->exists($path), 404);

        $downloadName = (string) Str::of(basename($document->original_name ?: 'seller-document'))
            ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
            ->limit(180, '');

        $contentType = in_array($document->mime_type, [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ], true) ? $document->mime_type : 'application/octet-stream';

        return Storage::disk('seller_documents')->download(
            $path,
            $downloadName,
            ['Content-Type' => $contentType]
        );
    }

    /**
     * Handle document uploads and submit for review.
     */
    public function upload(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop || !$shop->canSubmitOnboardingDocuments()) {
            flash(translate('Your onboarding submission cannot be changed while it is under review or approved.'))->warning();
            return back();
        }
        
        $rejectedDocumentTypes = $shop->rejectedOnboardingDocumentTypes();

        if ($shop->approval_status === 'under_review' && $rejectedDocumentTypes === []) {
            flash(translate('Your documents are currently under review. Please wait.'))->warning();
            return back();
        }

        // Only allow if pending or rejected (and can resubmit)
        if ($shop->approval_status === 'rejected' && !$shop->canResubmit()) {
            flash(translate('You have exceeded the maximum number of resubmission attempts.'))->error();
            return back();
        }

        // Initial submission requires the complete package. Corrections are
        // intentionally incremental: a seller may replace one rejected
        // document at a time while the other rejected versions remain
        // preserved for review history.
        $requiredTypes = $shop->approval_status === 'pending'
            ? $shop->requiredDocumentTypes()
            : [];

        $rules = [
            'contract'              => (in_array('contract', $requiredTypes, true) ? 'required' : 'nullable') . '|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:10240',
            'government_id'         => (in_array('government_id', $requiredTypes, true) ? 'required' : 'nullable') . '|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:10240',
            'business_registration' => (in_array('business_registration', $requiredTypes, true) ? 'required' : 'nullable') . '|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:10240',
            'certification'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:10240',
        ];

        $messages = [
            'contract.required'              => translate('The signed contract is mandatory.'),
            'government_id.required'         => translate('A government-issued ID is mandatory.'),
            'business_registration.required' => translate('Business registration documents are mandatory.'),
            'max'                            => translate('File size cannot exceed 10MB.'),
            'mimes'                          => translate('Only PDF, JPG, and PNG files are allowed.'),
        ];

        $request->validate($rules, $messages);

        if (in_array($shop->approval_status, ['rejected', 'under_review'], true)
            && ($requiredTypes !== [] || $rejectedDocumentTypes !== [])
            && !$request->hasFile('contract')
            && !$request->hasFile('government_id')
            && !$request->hasFile('business_registration')
            && !$request->hasFile('certification')) {
            return back()->withErrors(['documents' => translate('Upload at least one corrected document.')]);
        }

        try {
            DB::transaction(function () use ($request, &$shop) {
                // Serialize submissions for this shop so replacement versions
                // cannot be assigned the same number concurrently.
                $shop = $shop->newQuery()->lockForUpdate()->findOrFail($shop->id);
                $wasRejected = $shop->approval_status === 'rejected';

                foreach (['contract', 'government_id', 'business_registration', 'certification'] as $type) {
                    if ($request->hasFile($type)) {
                        $this->storeDocument($request, $shop, $type);
                    }
                }

                $shop->approval_status = 'under_review';
                $shop->documents_submitted_at = now();
                $shop->rejection_reason = null;
                $shop->admin_note = null;
                if ($wasRejected) {
                    $shop->resubmission_count = (int) $shop->resubmission_count + 1;
                }
                $shop->save();

                AuditLog::create([
                    'target_user_id' => $shop->user_id,
                    'action_type'    => 'seller_documents_uploaded',
                    'description'    => "Seller uploaded onboarding documents. Resubmission count: {$shop->resubmission_count}",
                    'ip_address'     => request()->ip(),
                ]);
            });

            app(\App\Services\SellerOnboardingNotifier::class)->documentsSubmitted($shop);

            flash(translate('Documents submitted successfully. We will review them shortly.'))->success();
            return back();

        } catch (\Exception $e) {
            Log::error('Document upload failed: ' . $e->getMessage());
            flash(translate('Failed to upload documents. Please try again.'))->error();
            return back();
        }
    }

    /**
     * Resubmit rejected documents.
     */
    public function resubmit(Request $request)
    {
        return $this->upload($request);
    }

    /**
     * Helper to store a single document.
     */
    private function storeDocument(Request $request, Shop $shop, string $type)
    {
        if ($request->hasFile($type)) {
            $file = $request->file($type);
            $previous = SellerDocument::where('shop_id', $shop->id)
                ->where('document_type', $type)
                ->latest('version')->first();
            $path = $file->store('', 'seller_documents');

            SellerDocument::create([
                'shop_id' => $shop->id,
                'document_type' => $type,
                'file_path' => $path,
                'original_name' => (string) Str::of(basename($file->getClientOriginalName()))
                    ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
                    ->limit(180, ''),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_at' => now(),
                'status' => 'pending',
                'version' => $previous ? ((int) $previous->version + 1) : 1,
                'replaces_document_id' => $previous?->id,
            ]);
        }
    }
}
