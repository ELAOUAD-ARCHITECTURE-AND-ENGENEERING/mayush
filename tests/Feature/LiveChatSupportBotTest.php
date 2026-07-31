<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Enums\BotState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use App\Events\NewCustomerMessageReceived;

class LiveChatSupportBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->disableCookieEncryption();

        // Seed support categories & cases
        $this->seedSupportDatabase();
    }

    protected function seedSupportDatabase(): void
    {
        DB::table('case_escalation_rules')->delete();
        DB::table('case_resolution_steps')->delete();
        DB::table('case_required_fields')->delete();
        DB::table('support_cases')->delete();
        DB::table('support_categories')->delete();

        $catId = DB::table('support_categories')->insertGetId([
            'code' => 'OR',
            'name' => 'Orders',
            'description' => 'Status, cancellations, missing items',
            'display_order' => 1
        ]);

        $caseId = DB::table('support_cases')->insertGetId([
            'category_id' => $catId,
            'case_code' => 'OR-002',
            'name' => 'Order Status',
            'description' => 'Check order status',
            'eligible_user_types' => 'all',
            'priority' => 'normal',
            'department' => 'Support',
            'status' => 'active',
            'version' => 1
        ]);

        DB::table('case_required_fields')->insert([
            'case_id' => $caseId,
            'field_key' => 'order_reference',
            'label' => 'Order Reference',
            'field_type' => 'text',
            'required' => true,
            'bot_prompt' => 'Please provide your order reference number (e.g. ORD-1234).',
            'display_order' => 1
        ]);

        DB::table('case_resolution_steps')->insert([
            'case_id' => $caseId,
            'step_order' => 1,
            'step_type' => 'action',
            'action_key' => 'getOrderStatus',
            'success_transition' => 'confirm'
        ]);
    }

    /** @test */
    public function it_initiates_chat_and_creates_greeting_message()
    {
        $response = $this->postJson(route('livechat.initiate'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'conversation',
            'messages',
            'user_avatar',
            'agent_avatar'
        ]);

        $this->assertDatabaseHas('support_conversations', [
            'status' => 'open',
            'conversation_state' => BotState::BOT_GREETING->value
        ]);

        $this->assertDatabaseHas('support_messages', [
            'sender_type' => 'system'
        ]);
    }

    /** @test */
    public function it_responds_in_active_language_french()
    {
        App::setLocale('fr');
        session()->put('locale', 'fr');

        $response = $this->postJson(route('livechat.initiate'));
        $response->assertStatus(200);

        $messages = $response->json('messages');
        $this->assertNotEmpty($messages);
        
        $greeting = $messages[0]['message'];
        $this->assertIsString($greeting);
    }

    /** @test */
    public function it_handles_category_selection_and_transitions_to_cases()
    {
        $conversation = SupportConversation::create([
            'status' => 'open',
            'conversation_state' => BotState::BOT_GREETING->value,
            'last_activity_at' => now()
        ]);

        $engine = new \App\Services\Bot\BotFlowEngine();
        $engine->process($conversation, 'Orders');

        $conversation->refresh();
        $this->assertEquals(BotState::INTENT_SELECTION->value, $conversation->conversation_state);
    }

    /** @test */
    public function it_collects_data_and_executes_resolution()
    {
        $conversation = SupportConversation::create([
            'status' => 'open',
            'conversation_state' => BotState::INTENT_SELECTION->value,
            'last_activity_at' => now()
        ]);

        $case = DB::table('support_cases')->where('case_code', 'OR-002')->first();

        $engine = new \App\Services\Bot\BotFlowEngine();

        // Select case "Order Status"
        $engine->process($conversation, $case->name);
        $conversation->refresh();

        $this->assertEquals(BotState::COLLECTING_DATA->value, $conversation->conversation_state);
        $this->assertEquals($case->id, $conversation->active_case_id);

        // Provide requested field: Order Reference "ORD-9999"
        $engine->process($conversation, 'ORD-9999');
        $conversation->refresh();

        // Should transition to WAITING_CONFIRMATION after executing resolution
        $this->assertEquals(BotState::WAITING_CONFIRMATION->value, $conversation->conversation_state);

        $this->assertGreaterThan(0, $conversation->messages()->where('sender_type', 'system')->count());
    }

    /** @test */
    public function it_escalates_to_human_agent_when_requested()
    {
        $conversation = SupportConversation::create([
            'guest_token' => 'test-guest-token',
            'status' => 'open',
            'bot_enabled' => true,
            'conversation_state' => BotState::BOT_GREETING->value,
            'last_activity_at' => now()
        ]);

        $engine = new \App\Services\Bot\BotFlowEngine();
        $engine->process($conversation, 'speak to human agent');

        $conversation->refresh();

        $this->assertFalse((bool)$conversation->bot_enabled);
        $this->assertEquals(BotState::WAITING_FOR_AGENT->value, $conversation->conversation_state);

        $this->assertDatabaseHas('chatbot_escalations', [
            'conversation_id' => $conversation->id
        ]);
    }

    /** @test */
    public function it_escalates_on_multilingual_frustration()
    {
        $conversation = SupportConversation::create([
            'guest_token' => 'test-guest-token',
            'status' => 'open',
            'bot_enabled' => true,
            'conversation_state' => BotState::BOT_GREETING->value,
            'last_activity_at' => now()
        ]);

        $engine = new \App\Services\Bot\BotFlowEngine();
        $engine->process($conversation, 'This is useless and unacceptable');

        $conversation->refresh();

        $this->assertFalse((bool)$conversation->bot_enabled);
        $this->assertEquals(BotState::WAITING_FOR_AGENT->value, $conversation->conversation_state);
    }

    /** @test */
    public function it_redacts_sensitive_credit_card_and_cvv_numbers()
    {
        $security = new \App\Services\Bot\SecurityGuard();
        
        $cleanCard = $security->sanitizeMessage('My card number is 4532-0123-4567-8901 please check');
        $this->assertStringContainsString('[REDACTED CARD NUMBER]', $cleanCard);
        $this->assertStringNotContainsString('4532-0123-4567-8901', $cleanCard);

        $cleanCvv = $security->sanitizeMessage('My security code CVV is 987');
        $this->assertStringContainsString('[REDACTED]', $cleanCvv);
        $this->assertStringNotContainsString('987', $cleanCvv);
    }

    /** @test */
    public function it_restarts_active_conversation()
    {
        $user = \App\Models\User::factory()->create();
        $conversation = SupportConversation::create([
            'user_id' => $user->id,
            'status' => 'open',
            'conversation_state' => BotState::BOT_GREETING->value,
            'last_activity_at' => now()
        ]);

        $response = $this->actingAs($user)
                         ->postJson(route('livechat.restart'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $conversation->refresh();
        $this->assertEquals('closed', $conversation->status);
    }
}
