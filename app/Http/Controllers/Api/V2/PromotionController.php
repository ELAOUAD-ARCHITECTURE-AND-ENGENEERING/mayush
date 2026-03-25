<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Promotion::with('product');
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sort = $request->sort ?? 'desc';
        $promotions = $query->orderBy('created_at', $sort)->paginate(15);

        return response()->json($promotions);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'product_id' => 'required|exists:customer_products,id',
            'tier' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = auth()->user();

        // 1. Check Credits
        if ($user->remaining_uploads <= 0) {
            return response()->json([
                'result' => false, 
                'message' => translate('Insufficient credits. Please top up your account.'),
                'action' => 'top_up'
            ], 403);
        }

        // 2. Check Max Active Promotions (10)
        $activePromotionsCount = \App\Models\Promotion::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'awaiting_admin_review'])
            ->where('end_date', '>', \Carbon\Carbon::now())
            ->count();

        if ($activePromotionsCount >= 10) {
            return response()->json(['result' => false, 'message' => translate('You have reached the maximum limit of 10 active promotions.')], 403);
        }

        // 3. Check Date Overlap
        $productId = $request->product_id;
        
        $overlap = \App\Models\Promotion::where('product_id', $productId)
            ->whereIn('status', ['approved', 'awaiting_admin_review'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['result' => false, 'message' => translate('Promotion dates overlap with an existing promotion for this product.')], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $promotion = new \App\Models\Promotion();
            $promotion->user_id = $user->id;
            $promotion->product_id = $productId;
            $promotion->tier = $request->tier;
            $promotion->start_date = $request->start_date;
            $promotion->end_date = $request->end_date;
            $promotion->notes = $request->notes;
            $promotion->status = 'awaiting_admin_review';
            $promotion->save();

            // Deduct Credit
            $user->remaining_uploads -= 1;
            $user->save();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'result' => true,
                'message' => translate('Promotion request submitted successfully.'),
                'promotion' => $promotion
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['result' => false, 'message' => translate('Something went wrong: ') . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // C-5 FIX: Admin role guard — only admins can change promotion status
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->user_type !== 'admin') {
            return response()->json(['result' => false, 'message' => translate('Unauthorized. Admin access required.')], 403);
        }

        $promotion = \App\Models\Promotion::with('user')->findOrFail($id);
        
        if ($request->has('status')) {
            $status = $request->status;
            if (in_array($status, ['approved', 'rejected', 'expired'])) {
                $oldStatus = $promotion->status;
                $promotion->status = $status;
                $promotion->save();
                
                // Refund credit if rejected
                if ($status == 'rejected' && $oldStatus != 'rejected') {
                    $promotion->user->remaining_uploads += 1;
                    $promotion->user->save();
                }
            }
        }

        return response()->json([
            'result' => true,
            'message' => translate('Promotion status updated.'),
            'promotion' => $promotion
        ]);
    }
}
