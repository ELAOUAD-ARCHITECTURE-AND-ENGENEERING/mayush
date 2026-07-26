<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SemanticEmbedding;
use App\Utility\SemanticUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_config_has_openrouter_block(): void
    {
        $this->assertIsArray(config('services.openrouter'));
        $this->assertArrayHasKey('key', config('services.openrouter'));
    }

    public function test_generate_embedding_returns_768_dimensions_with_mocked_api(): void
    {
        Config::set('services.openrouter.key', 'TEST_KEY');
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'data' => [['embedding' => array_fill(0, 768, 0.1)]],
            ], 200),
        ]);

        $vector = SemanticUtility::generateEmbedding('handmade ceramic mug with floral pattern');

        $this->assertIsArray($vector);
        $this->assertCount(768, $vector);
        $this->assertIsFloat($vector[0]);
    }

    public function test_fallback_returns_32_dim_mock_when_no_key(): void
    {
        Config::set('services.openrouter.key', '');

        $vector = SemanticUtility::generateEmbedding('test product description');

        $this->assertIsArray($vector);
        $this->assertCount(32, $vector);
    }

    public function test_generate_embedding_returns_empty_on_invalid_key(): void
    {
        Config::set('services.openrouter.key', 'INVALID_KEY_12345');
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'error' => ['message' => 'API key invalid'],
            ], 403),
        ]);

        $vector = SemanticUtility::generateEmbedding('test product');

        $this->assertIsArray($vector);
        $this->assertEmpty($vector);
    }

    public function test_extract_text_truncates_at_2000_chars(): void
    {
        $product = new Product();
        $product->name = str_repeat('A', 3000);
        $product->description = 'Some description';
        $product->tags = 'tag1,tag2';

        $this->assertLessThanOrEqual(2000, strlen(SemanticUtility::extractText($product)));
    }

    public function test_extract_text_strips_html_tags(): void
    {
        $product = new Product();
        $product->name = 'Test Product';
        $product->description = '<p>This is <strong>bold</strong> and <em>italic</em>.</p>';
        $product->tags = 'tag1';

        $text = SemanticUtility::extractText($product);

        $this->assertStringNotContainsString('<p>', $text);
        $this->assertStringNotContainsString('<strong>', $text);
        $this->assertStringContainsString('bold', $text);
    }

    public function test_cosine_similarity_identical_vectors(): void
    {
        $vec = [0.5, 0.3, 0.8, 0.1];

        $this->assertEqualsWithDelta(1.0, SemanticUtility::calculateSimilarity($vec, $vec), 0.001);
    }

    public function test_cosine_similarity_orthogonal_vectors(): void
    {
        $this->assertEqualsWithDelta(0.0, SemanticUtility::calculateSimilarity([1.0, 0.0], [0.0, 1.0]), 0.001);
    }

    public function test_cosine_similarity_opposite_vectors(): void
    {
        $this->assertEqualsWithDelta(-1.0, SemanticUtility::calculateSimilarity([1.0, 0.0], [-1.0, 0.0]), 0.001);
    }

    public function test_cosine_similarity_zero_vector_returns_zero(): void
    {
        $this->assertEquals(0, SemanticUtility::calculateSimilarity([1.0, 2.0, 3.0], [0.0, 0.0, 0.0]));
    }

    public function test_sync_embedding_creates_db_record(): void
    {
        Config::set('services.openrouter.key', '');
        $product = Product::factory()->create();

        $this->assertTrue(SemanticUtility::syncEmbedding($product));

        $embedding = SemanticEmbedding::where('embeddable_type', Product::class)
            ->where('embeddable_id', $product->id)
            ->first();

        $this->assertNotNull($embedding);
        $this->assertNotEmpty($embedding->vector);
        $this->assertNotEmpty($embedding->content);
        $this->assertIsArray(json_decode($embedding->vector, true));
    }

    public function test_sync_embedding_updates_existing_record(): void
    {
        Config::set('services.openrouter.key', '');
        $product = Product::factory()->create();

        SemanticUtility::syncEmbedding($product);
        SemanticUtility::syncEmbedding($product);

        $this->assertSame(1, SemanticEmbedding::where('embeddable_type', Product::class)
            ->where('embeddable_id', $product->id)
            ->count());
    }

    public function test_similarity_threshold_filters_low_scores(): void
    {
        Config::set('services.openrouter.key', '');
        $product = Product::factory()->create();
        SemanticUtility::syncEmbedding($product);

        $results = SemanticUtility::search('completely unrelated gibberish xyzzy', 10);

        $this->assertIsArray($results);
        foreach ($results as $result) {
            $this->assertGreaterThan(0.68, $result['score']);
        }
    }

    public function test_search_returns_model_in_results(): void
    {
        Config::set('services.openrouter.key', '');
        $product = Product::factory()->create(['name' => 'Handmade Ceramic Mug']);
        $vector = SemanticUtility::generateEmbedding($product->name);

        SemanticEmbedding::create([
            'embeddable_type' => Product::class,
            'embeddable_id' => $product->id,
            'vector' => json_encode($vector),
            'content' => $product->name,
            'content_hash' => hash('sha256', $product->name),
            'metadata' => json_encode([]),
        ]);

        $results = SemanticUtility::search($product->name, 5);

        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('score', $results[0]);
        $this->assertArrayHasKey('model', $results[0]);
        $this->assertTrue($results[0]['model']->is($product));
    }

    public function test_reindex_command_runs_without_error(): void
    {
        Config::set('services.openrouter.key', '');
        Product::factory()->create();

        $this->assertSame(0, Artisan::call('search:reindex'));
    }

    public function test_stored_embeddings_have_correct_dimensions(): void
    {
        Config::set('services.openrouter.key', '');
        $product = Product::factory()->create();
        SemanticUtility::syncEmbedding($product);

        $vector = json_decode(SemanticEmbedding::firstOrFail()->vector, true);

        $this->assertIsArray($vector);
        $this->assertCount(32, $vector);
    }
}
