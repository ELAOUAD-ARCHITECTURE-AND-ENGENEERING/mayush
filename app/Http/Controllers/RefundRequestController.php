<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function admin_index() {}
    public function refund_config() {}
    public function paid_index() {}
    public function rejected_index() {}
    public function reason_view($id) {}
    public function reject_refund_request() {}
    public function reject_reason_view($id) {}
    public function refund_pay() {}
    public function refund_time_update() {}
    public function refund_sticker_update() {}
    public function categoriesWiseProductRefund() {}
    public function updateRefundSettings() {}
    public function checkRefundableCategory() {}
    public function request_store($id) {}
    public function customer_index() {}
    public function refund_request_send_page($id) {}
    public function vendor_index() {}
    public function seller_refund_configuration() {}
    public function sellerCategoriesWiseProductRefund() {}
    public function request_approval_vendor() {}
    public function checkSellerRefundableCategory() {}
}
