<?php

namespace Tests\Feature;

use App\Mail\InvoiceEmailManager;
use App\Mail\MailManager;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use App\Utility\EmailUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class FrenchEmailTemplatesTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_email_templates_in_database_contain_french_copy(): void
    {
        $templates = EmailTemplate::query()->whereIn('identifier', [
            'registration_email_to_customer',
            'order_placed_email_to_customer',
            'password_reset_email_to_all',
        ])->get()->keyBy('identifier');

        $this->assertStringContainsString('Bienvenue sur', $templates['registration_email_to_customer']->subject);
        $this->assertStringContainsString('Votre commande', $templates['order_placed_email_to_customer']->subject);
        $this->assertStringContainsString('Réinitialisation', $templates['password_reset_email_to_all']->subject);
    }

    public function test_mail_manager_forces_french_locale_when_building(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'customer.fr@example.com',
            'name' => 'Jean Dupont',
        ]);

        EmailUtility::customer_registration_email('registration_email_to_customer', $user);

        Mail::assertQueued(MailManager::class, function (MailManager $mail) {
            $mail->build();
            $this->assertSame('fr', App::getLocale());
            $this->assertStringContainsString('Bienvenue sur', $mail->array['subject']);
            return true;
        });
    }

    public function test_password_reset_flow_queues_french_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'reset.fr@example.com',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        Mail::assertQueued(MailManager::class, function (MailManager $mail) {
            $mail->build();
            $this->assertSame('fr', App::getLocale());
            $this->assertStringContainsString('Réinitialisation', $mail->array['subject']);
            return true;
        });
    }

    public function test_invoice_email_renders_french_labels(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $order = new Order();
        $order->id = 999;
        $order->code = '20260722-123456';
        $order->user_id = $user->id;
        $order->date = time();
        $order->grand_total = 150.00;
        $order->shipping_address = json_encode([
            'name' => 'Jean Dupont',
            'address' => '15 Rue de Paris',
            'city' => 'Casablanca',
            'country' => 'Maroc',
            'email' => 'jean@example.com',
            'phone' => '+212600000000',
        ]);

        $mailable = new InvoiceEmailManager([
            'view' => 'emails.invoice',
            'subject' => 'Facture pour commande 20260722-123456',
            'order' => $order,
        ]);

        $rendered = $mailable->render();

        $this->assertStringContainsString('N° de commande', $rendered);
        $this->assertStringContainsString('Date de commande', $rendered);
        $this->assertStringContainsString('Total général', $rendered);
    }
}
