<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportConversation;
use Auth;
use Carbon\Carbon;

class AdminLiveChatController extends Controller
{
    public function index()
    {
        $conversations = SupportConversation::orderBy('last_activity_at', 'desc')->get();
        return view('backend.support.livechat.index', compact('conversations'));
    }

    public function fetchMessages($id)
    {
        $conversation = SupportConversation::with('messages')->findOrFail($id);
        
        // Mark user/guest messages as read by admin
        $conversation->messages()
            ->whereIn('sender_type', ['user', 'guest'])
            ->where('seen', false)
            ->update(['seen' => true]);

        return response()->json([
            'messages' => $conversation->messages()->get(),
            'state' => $conversation->conversation_state,
            'reason' => $conversation->escalation_reason,
            'language' => $conversation->language,
            'frustration' => $conversation->frustration_score
        ]);
    }

    public function reply(Request $request, $id)
    {
        $conversation = SupportConversation::findOrFail($id);
        
        $conversation->last_activity_at = Carbon::now();
        if ($conversation->status === 'expired' || $conversation->status === 'closed') {
            $conversation->status = 'open';
        }
        $conversation->save();

        // Mark user/guest messages as read when admin replies
        $conversation->messages()
            ->whereIn('sender_type', ['user', 'guest'])
            ->where('seen', false)
            ->update(['seen' => true]);

        $message = $conversation->messages()->create([
            'sender_type' => 'agent',
            'sender_id' => Auth::id(),
            'message' => $request->message
        ]);

        return response()->json(['message' => $message]);
    }

    public function close($id)
    {
        $conversation = SupportConversation::findOrFail($id);
        $conversation->status = 'closed';
        $conversation->save();
        
        $conversation->messages()->create([
            'sender_type' => 'system',
            'message' => 'conversation closed by agent'
        ]);

        return redirect()->back()->with('success', 'Conversation closed successfully.');
    }
}
