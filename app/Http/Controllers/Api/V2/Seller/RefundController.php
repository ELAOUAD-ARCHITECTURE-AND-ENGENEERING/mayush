<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Resources\V2\RefundRequestCollection;
use Illuminate\Http\Request;

use App\Models\RefundRequest;


class RefundController extends Controller
{
    public function index(){
        $sellerId = auth()->user()->id;

        $refunds = RefundRequest::where('seller_id',$sellerId)->latest()->paginate(10);
        return new RefundRequestCollection($refunds);
    }
    
    public function request_approval_vendor(Request $request)
    {
        $refund = RefundRequest::findOrFail($request->refund_id);
        $role = (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') ? 'admin' : 'seller';

        if ($role === 'admin') {
            $refund->seller_approval = 1;
            $refund->admin_approval = 1;
        }
        elseif (auth()->user()->user_type == 'seller' && $refund->seller_id == auth()->user()->id) {
            $refund->seller_approval = 1;
        }

        if ($refund->save()) 
        {
            try {
                \App\Utility\EmailUtility::sendRefundAcceptedEmails($refund, $role);
            } catch (\Exception $e) {
                \Log::error("Failed to send refund accepted emails: " . $e->getMessage());
            }

            return $this->success(translate('Refund Status has been change successfully'));
        }
        else {
            return $this->failed(translate('Refund Status change failed!'));
        }
    }

    public function reject_refund_request(Request $request) {
        $refund = RefundRequest::findOrFail($request->refund_id);
        $refund->reject_reason = $request->reject_reason;
        $role = (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') ? 'admin' : 'seller';

        if ($role === 'admin') {
            $refund->admin_approval = 2;
            $refund->refund_status = 2;
        }
        elseif (auth()->user()->user_type == 'seller' && $refund->seller_id == auth()->user()->id) {
            $refund->seller_approval = 2;
        }
      
        if ($refund->save()) 
        {
            try {
                \App\Utility\EmailUtility::sendRefundDeniedEmails($refund, $role);
            } catch (\Exception $e) {
                \Log::error("Failed to send refund denied emails: " . $e->getMessage());
            }

            return $this->success(translate('Refund Status has been change successfully'));
        }
        else {
            return $this->failed(translate('Refund Status change failed!'));
        }
    }

}
