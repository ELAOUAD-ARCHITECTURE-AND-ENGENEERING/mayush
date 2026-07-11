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
            ->assertSee(route('contact'), false);
    }

    public function test_guest_can_submit_valid_contact_form(): void
    {
        Mail::fake();
        User::factory()->admin()->create(['email' => 'admin@example.test']);

        $response = $this->from('/contact-us')->post(route('contact'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'phone' => '+15551234567',
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
            ->post(route('contact'), [
                'name' => '',
                'email' => 'not-an-email',
                'content' => '',
            ])
            ->assertRedirect('/contact-us')
            ->assertSessionHasErrors(['name', 'email', 'content']);

        $this->assertSame(0, Contact::count());
    }

    public function test_public_contact_route_is_not_admin_get_route(): void
    {
        $publicRoute = Route::getRoutes()->getByName('contact');

        $this->assertNotNull($publicRoute);
        $this->assertContains('POST', $publicRoute->methods());
        $this->assertSame('contact', $publicRoute->uri());
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
            'phone' => '+15550001111',
            'email' => 'hello@example.test',
        ]);
        $page->meta_title = 'Contact Mayush';
        $page->meta_description = 'Contact Mayush support';
        $page->tags = 'contact,support';
        $page->save();

        return $page;
    }
}
