<?php
$conv = new \App\Models\SupportConversation();
$conv->status = 'open';
$conv->bot_enabled = true;
$conv->guest_token = 'test-token-123';
$conv->save();

event(new \App\Events\NewCustomerMessageReceived($conv, 'where is my order?'));

$count = \App\Models\SupportMessage::where('support_conversation_id', $conv->id)->where('sender_type', 'system')->count();
echo "System messages created: $count\n";

$messages = \App\Models\SupportMessage::where('support_conversation_id', $conv->id)->get();
foreach ($messages as $msg) {
    echo "Message: " . $msg->message . "\n";
}
