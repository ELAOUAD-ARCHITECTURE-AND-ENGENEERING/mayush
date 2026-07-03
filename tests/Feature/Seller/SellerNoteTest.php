<?php

namespace Tests\Feature\Seller;

use App\Models\Note;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class SellerNoteTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        $this->seedConfigsWithSellerNotesEnabled();
    }

    public function test_seller_can_list_own_notes(): void
    {
        $seller = $this->sellerWithShop();
        $this->createNote([
            'user_id' => $seller->id,
            'note_type' => 'general',
            'description' => 'Own seller note',
        ]);

        $this->actingAs($seller)
            ->get(route('seller.note.index'))
            ->assertOk()
            ->assertSee('Own seller note');
    }

    public function test_seller_can_view_own_note(): void
    {
        $seller = $this->sellerWithShop();
        $note = $this->createNote([
            'user_id' => $seller->id,
            'note_type' => 'general',
            'description' => 'Private note body',
        ]);

        $this->actingAs($seller)
            ->getJson(route('seller.note.show', $note->id))
            ->assertOk()
            ->assertJson([
                'id' => $note->id,
                'description' => 'Private note body',
            ]);
    }

    public function test_seller_can_create_update_and_delete_own_note(): void
    {
        $seller = $this->sellerWithShop();

        $createResponse = $this->actingAs($seller)->post(route('seller.note.store'), [
            'note_type' => 'shipping',
            'description' => 'Initial seller note',
        ]);

        $createResponse->assertRedirect(route('seller.note.index'));
        $note = Note::where('user_id', $seller->id)->firstOrFail();
        $this->assertSame('shipping', $note->note_type);
        $this->assertSame('Initial seller note', $note->description);

        $this->actingAs($seller)
            ->patch(route('seller.note.update', $note->id), [
                'note_type' => 'delivery',
                'description' => 'Updated seller note',
                'lang' => env('DEFAULT_LANGUAGE', 'en'),
            ])
            ->assertRedirect();

        $note->refresh();
        $this->assertSame('delivery', $note->note_type);
        $this->assertSame('Updated seller note', $note->getTranslation('description'));

        $this->actingAs($seller)
            ->delete(route('seller.note.destroy', $note->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_seller_cannot_view_another_sellers_note(): void
    {
        $seller = $this->sellerWithShop();
        $otherSeller = $this->sellerWithShop();
        $note = $this->createNote([
            'user_id' => $otherSeller->id,
            'note_type' => 'general',
            'description' => 'Other seller note',
        ]);

        $this->actingAs($seller)
            ->getJson(route('seller.note.show', $note->id))
            ->assertNotFound();
    }

    public function test_seller_cannot_update_or_delete_another_sellers_note(): void
    {
        $seller = $this->sellerWithShop();
        $otherSeller = $this->sellerWithShop();
        $note = $this->createNote([
            'user_id' => $otherSeller->id,
            'note_type' => 'general',
            'description' => 'Other seller note',
        ]);

        $this->actingAs($seller)
            ->patch(route('seller.note.update', $note->id), [
                'note_type' => 'refund',
                'description' => 'Should not update',
                'lang' => env('DEFAULT_LANGUAGE', 'en'),
            ])
            ->assertNotFound();

        $this->actingAs($seller)
            ->delete(route('seller.note.destroy', $note->id))
            ->assertNotFound();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'description' => 'Other seller note',
        ]);
    }

    public function test_invalid_note_id_returns_404(): void
    {
        $seller = $this->sellerWithShop();

        $this->actingAs($seller)
            ->getJson(route('seller.note.show', 999999))
            ->assertNotFound();
    }

    public function test_note_view_route_and_markup_use_get_json_contract(): void
    {
        $seller = $this->sellerWithShop();
        $note = $this->createNote([
            'user_id' => $seller->id,
            'note_type' => 'general',
            'description' => 'Contract note',
        ]);

        $response = $this->actingAs($seller)->get(route('seller.note.index'));

        $response->assertOk()
            ->assertSee(route('seller.note.show', $note->id), false)
            ->assertSee('$.getJSON(url', false)
            ->assertSee(route('seller.note.destroy', $note->id), false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    private function seedConfigsWithSellerNotesEnabled(): void
    {
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'seller_can_add_note'],
            ['value' => '1']
        );
    }

    private function createNote(array $attributes): Note
    {
        $note = new Note();
        foreach ($attributes as $key => $value) {
            $note->{$key} = $value;
        }
        $note->save();

        return $note;
    }

    private function sellerWithShop(): User
    {
        $seller = User::factory()->seller()->create();
        Shop::factory()->create(['user_id' => $seller->id]);

        return $seller;
    }
}
