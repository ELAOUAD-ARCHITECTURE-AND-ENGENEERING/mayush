<?php

namespace Tests\Feature\Frontend;

use App\Mail\ContactMailManager;
use App\Models\Contact;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class ContactFormTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_guest_can_view_contact_page(): void
    {
        $this->createContactPage();

        $this->get('/contact-us')
            ->assertOk()
            ->assertSee(route('contact.store'), false)
            ->assertSee('id="contact-email"', false)
            ->assertSee('id="contact-phone"', false)
            ->assertSee('contact-email-feedback', false)
            ->assertSee('contact-phone-feedback', false)
            ->assertSee("email.addEventListener('input'", false)
            ->assertSee("phone.addEventListener('input'", false);
    }

    public function test_guest_can_submit_valid_contact_form(): void
    {
        Mail::fake();
        User::factory()->admin()->create(['email' => 'admin@example.test']);

        $response = $this->from('/contact-us')->post(route('contact.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'phone' => '+212612345678',
            'content' => 'I need help with an order.',
        ]);

        $response->assertRedirect('/contact-us');
        $this->assertDatabaseHas('contacts', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'content' => 'I need help with an order.',
        ]);
        Mail::assertQueued(ContactMailManager::class);
    }

    public function test_invalid_contact_submission_fails_validation(): void
    {
        $this->from('/contact-us')
            ->post(route('contact.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'phone' => '+15551234567',
                'content' => '',
            ])
            ->assertRedirect('/contact-us')
            ->assertSessionHasErrors(['name', 'email', 'phone', 'content']);

        $this->assertSame(0, Contact::count());
    }

    public function test_contact_phone_is_required(): void
    {
        $this->from('/contact-us')
            ->post(route('contact.store'), [
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.test',
                'phone' => '',
                'content' => 'I need help with an order.',
            ])
            ->assertRedirect('/contact-us')
            ->assertSessionHasErrors('phone');

        $this->assertSame(0, Contact::count());
    }

    public function test_public_contact_route_is_not_admin_get_route(): void
    {
        $publicRoute = Route::getRoutes()->getByName('contact.store');
        $adminRoute = Route::getRoutes()->getByName('contact');

        $this->assertNotNull($publicRoute);
        $this->assertContains('POST', $publicRoute->methods());
        $this->assertSame('contact', $publicRoute->uri());

        $this->assertNotNull($adminRoute);
        $this->assertContains('GET', $adminRoute->methods());
        $this->assertNotSame($adminRoute->uri(), $publicRoute->uri());
    }

    private function createContactPage(): Page
    {
        $page = new Page();
        $page->type = 'contact_us_page';
        $page->title = 'Contact Us';
        $page->slug = 'contact-us';
        $page->content = json_encode([
            'description' => 'Tell us what you need.',
            'address' => '123 Market Street',
            'phone' => '+212612345678',
            'email' => 'hello@example.test',
        ]);
        $page->meta_title = 'Contact Mayush';
        $page->meta_description = 'Contact Mayush support';
        $page->tags = 'contact,support';
        $page->save();

        return $page;
    }
}
