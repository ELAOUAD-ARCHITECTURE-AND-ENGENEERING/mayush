<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use Auth;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->user_type !== 'admin') {
                abort(403, 'Access Denied. Admin access required.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Promotion::with(['product', 'user']);

        // M-5 FIX: Allow filtering by status, default shows all
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $promotions = $query->latest()->paginate(15);
        $status_filter = $request->status ?? 'all';
        return view('backend.promotions.index', compact('promotions', 'status_filter'));
    }

    public function update_status(Request $request)
    {
        $promotion = Promotion::findOrFail($request->id);
        $oldStatus = $promotion->status;
        $promotion->status = $request->status;
        
        if ($promotion->save()) {
            // Refund credit if rejected
            if ($request->status == 'rejected' && $oldStatus != 'rejected') {
                $user = $promotion->user;
                $user->remaining_uploads += 1;
                $user->save();
            }
            return 1;
        }
        return 0;
    }
}
