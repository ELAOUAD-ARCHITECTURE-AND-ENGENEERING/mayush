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

        $documents = $shop->documents->keyBy('document_type');
        
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

    /**
     * Handle document uploads and submit for review.
     */
    public function upload(Request $request)
    {
        $shop = Auth::user()->shop;
        
        if ($shop->approval_status === 'under_review') {
            flash(translate('Your documents are currently under review. Please wait.'))->warning();
            return back();
        }

        // Only allow if pending or rejected (and can resubmit)
        if ($shop->approval_status === 'rejected' && !$shop->canResubmit()) {
            flash(translate('You have exceeded the maximum number of resubmission attempts.'))->error();
            return back();
        }

        $rules = [
            'contract'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'government_id'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'business_registration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certification'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        $messages = [
            'contract.required'              => translate('The signed contract is mandatory.'),
            'government_id.required'         => translate('A government-issued ID is mandatory.'),
            'business_registration.required' => translate('Business registration documents are mandatory.'),
            'max'                            => translate('File size cannot exceed 10MB.'),
            'mimes'                          => translate('Only PDF, JPG, and PNG files are allowed.'),
        ];

        $request->validate($rules, $messages);

        try {
            $this->storeDocument($request, $shop, 'contract');
            $this->storeDocument($request, $shop, 'government_id');
            $this->storeDocument($request, $shop, 'business_registration');
            
            if ($request->hasFile('certification')) {
                $this->storeDocument($request, $shop, 'certification');
            }

            // Update shop status
            $shop->approval_status = 'under_review';
            $shop->documents_submitted_at = now();
            
            if ($shop->approval_status === 'rejected') {
                $shop->resubmission_count += 1;
            }
            
            $shop->save();

            // Log action
            AuditLog::create([
                'target_user_id' => $shop->user_id,
                'action_type'    => 'seller_documents_uploaded',
                'description'    => "Seller uploaded onboarding documents. Resubmission count: {$shop->resubmission_count}",
                'ip_address'     => request()->ip(),
            ]);

            // Notify Admin
            try {
                EmailUtility::seller_documents_received_admin('seller_documents_received_admin', $shop);
            } catch (\Exception $e) {
                Log::error('Failed to send admin notification for documents: ' . $e->getMessage());
            }

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
            $path = $file->store('private/seller-documents');
            
            SellerDocument::updateOrCreate(
                ['shop_id' => $shop->id, 'document_type' => $type],
                [
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                    'uploaded_at'   => now(),
                ]
            );
        }
    }
}
