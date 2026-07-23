<?php

namespace App\Http\Controllers\Seller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Schema;
class NotificationController extends Controller
{
    public function index(Request $request) {
        $category = mb_substr((string) $request->query('category'), 0, 50);
        $severity = in_array($request->query('severity'), ['info', 'important', 'critical'], true)
            ? $request->query('severity')
            : null;
        $read = in_array($request->query('read'), ['read', 'unread'], true)
            ? $request->query('read')
            : null;
        $notifications = auth()->user()->notifications()
            ->when(Schema::hasColumn('notifications', 'archived_at'), fn ($query) => $query->whereNull('archived_at'))
            ->when($category !== '' && Schema::hasColumn('notifications', 'category'), fn ($query) => $query->where('category', $category))
            ->when($severity && Schema::hasColumn('notifications', 'severity'), fn ($query) => $query->where('severity', $severity))
            ->when($read === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($read === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        
        return view('seller.notification.index', compact('notifications'));
    }

    public function bulkDelete(Request $request){
        if($request->notification_ids){
            $query = auth()->user()->notifications()->whereIn('id', (array) $request->notification_ids);
            if (Schema::hasColumn('notifications', 'archived_at')) {
                $query->update(['archived_at' => now(), 'updated_at' => now()]);
            } else {
                $query->delete();
            }
        }
        return 1;
    }

    public function readAndRedirect($id) {
        $decorator = "App\Http\Controllers\NotificationController";
        return (new $decorator)->readAndRedirect($id);
    }
}
