<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class LiveChatController extends Controller
{
    public function initiate(Request $request)
    {
        $conversation = $this->getActiveConversation($request);
        
        if (!$conversation) {
            $conversation = new SupportConversation();
            $conversation->status = 'open';
            $conversation->last_activity_at = Carbon::now();
            
            if (Auth::check()) {
                $conversation->user_id = Auth::id();
            } else {
                $guestToken = $request->cookie('guest_token') ?? Str::random(40);
                $conversation->guest_token = $guestToken;
                Cookie::queue('guest_token', $guestToken, 60 * 24 * 365);
            }
            
            $conversation->save();
            
            // Trigger Bot Initial Greeting
            // Eloquent defaults aren't loaded until refresh, so we assume true for new chats
            $categories = \Illuminate\Support\Facades\DB::table('support_categories')
                ->orderBy('display_order')
                ->pluck('name')
                ->toArray();
                
            $messageBuilder = new \App\Services\Bot\MessageBuilder();
            $greeting = $messageBuilder->build(
                "Hello! I am your Mayush automated assistant. How can I help you today?",
                $categories
            );
            
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $greeting
            ]);
            
            $conversation->conversation_state = \App\Enums\BotState::BOT_GREETING->value;
            $conversation->save();
        }
        
        // Force refresh messages so the newly created greeting is included in the JSON payload
        $conversation->load('messages', 'user', 'agent');
        $userAvatar = $conversation->user ? uploaded_asset($conversation->user->avatar_original) : null;
        $agentAvatar = null;
        if ($conversation->agent) {
            $agentAvatar = uploaded_asset($conversation->agent->avatar_original) ?: static_asset('assets/img/avatar-place.png');
        }
        
        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'user_avatar' => $userAvatar,
            'agent_avatar' => $agentAvatar
        ]);
    }

    public function restart(Request $request)
    {
        $conversation = $this->getActiveConversation($request);
        if ($conversation) {
            $conversation->status = 'closed';
            $conversation->save();
        }
        return response()->json(['success' => true]);
    }

    public function sendMessage(Request $request)
    {
        $conversation = $this->getActiveConversation($request);
        if (!$conversation) {
            return response()->json(['error' => 'No active conversation'], 404);
        }

        $conversation->last_activity_at = Carbon::now();
        $conversation->save();

        $senderType = Auth::check() ? 'user' : 'guest';

        $message = $conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => Auth::check() ? Auth::id() : null,
            'message' => $request->message,
            'seen' => $conversation->bot_enabled ? true : false // If bot is enabled, it reads instantly
        ]);

        if ($conversation->bot_enabled) {
            event(new \App\Events\NewCustomerMessageReceived($conversation, $request->message));
        }

        return response()->json(['message' => $message]);
    }

    public function fetchMessages(Request $request)
    {
        $conversation = $this->getActiveConversation($request);
        if (!$conversation) {
            return response()->json(['messages' => []]);
        }

        $conversation->load('user', 'agent');
        $userAvatar = $conversation->user ? uploaded_asset($conversation->user->avatar_original) : null;
        
        $agentAvatar = null;
        if ($conversation->agent) {
            $agentAvatar = uploaded_asset($conversation->agent->avatar_original) ?: static_asset('assets/img/avatar-place.png');
        }

        return response()->json([
            'messages' => $conversation->messages,
            'user_avatar' => $userAvatar,
            'agent_avatar' => $agentAvatar
        ]);
    }
    
    public function ping(Request $request)
    {
        $conversation = $this->getActiveConversation($request);
        if ($conversation) {
            $conversation->last_activity_at = Carbon::now();
            $conversation->save();
        }
        
        return response()->json(['status' => 'ok']);
    }

    private function getActiveConversation(Request $request)
    {
        if (Auth::check()) {
            return SupportConversation::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();
        }
        
        $guestToken = $request->cookie('guest_token');
        if ($guestToken) {
            return SupportConversation::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->where('status', 'open')
                ->first();
        }
        
        return null;
    }
}
