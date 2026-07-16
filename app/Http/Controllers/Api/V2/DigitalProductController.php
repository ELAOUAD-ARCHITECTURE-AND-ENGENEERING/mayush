<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;

class DigitalProductController extends Controller
{
    public function download(Request $request)
    {
        $product = Product::where('digital', 1)->findOrFail($request->id);
        $orders = Order::select("id")->where('user_id', auth()->user()->id)->pluck('id');
        $orderDetails = OrderDetail::where("product_id", $request->id)->whereIn("order_id", $orders)->get();
        $isAdmin = auth()->user()->user_type == 'admin';
        $isApprovedOwner = auth()->user()->id == $product->user_id && $product->isPubliclyVisible();
        $hasPaidOrder = $orderDetails->where('payment_status', 'paid')->isNotEmpty();

        if (!$isAdmin && auth()->user()->id == $product->user_id && !$isApprovedOwner) {
            return response()->json([
                'message' => translate('Seller onboarding is not complete.'),
                'error' => 'seller_onboarding_incomplete',
            ], 403);
        }

        if ($isAdmin || $isApprovedOwner || $hasPaidOrder) {
            $upload = Upload::findOrFail($product->file_name);
            if (env('FILESYSTEM_DRIVER') == "s3") {
                return \Storage::disk(config('filesystems.default'))->download($upload->file_name, $upload->file_original_name . "." . $upload->extension);
            } else {
                if (file_exists(base_path('public/' . $upload->file_name))) {
                    $file = public_path() . "/$upload->file_name";
                    return response()->download($file, config('app.name') . "_" . $upload->file_original_name . "." . $upload->extension);
                }
            }
        } else {
            return response()->json([
                'message' => translate('You are not allowed to download this product.'),
                'error' => 'digital_download_forbidden',
            ], 403);
        }
    }
}
