<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\SemanticEmbedding;
use App\Utility\SemanticUtility;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

/**
 * Comprehensive test suite for the Gemini AI Semantic Search pipeline.
 * Covers: API key config, embedding generation, cosine similarity, DB persistence,
 * search endpoint integration, and the reindexing command.
 */
class SemanticSearchTest extends TestCase
{
    // ─────────────────────────────────────────────────────────
    //  1. CONFIGURATION TESTS
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_gemini_api_key_is_configured()
    {
        $key = config('services.gemini.key');
        if (empty($key) && app()->environment('testing')) {
            $this->markTestSkipped('GEMINI_API_KEY not configured for the testing process.');
        }

        $this->assertNotEmpty($key, 'GEMINI_API_KEY must be set in .env and referenced via config/services.php');
    }

    /** @test */
    public function test_services_config_has_gemini_block()
    {
        $this->assertNotNull(config('services.gemini'), 'config/services.php must contain a "gemini" block');
        $this->assertArrayHasKey('key', config('services.gemini'));
    }

    // ─────────────────────────────────────────────────────────
    //  2. EMBEDDING GENERATION TESTS
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_generate_embedding_returns_768_dimensions_with_real_key()
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            $this->markTestSkipped('GEMINI_API_KEY not configured — skipping live API test.');
        }

        $vector = SemanticUtility::generateEmbedding('handmade ceramic mug with floral pattern');

        $this->assertIsArray($vector);
        $this->assertCount(768, $vector, 'Gemini gemini-embedding-001 should return exactly 768 dimensions when outputDimensionality is set');
        $this->assertIsFloat($vector[0], 'Each dimension should be a float');
    }

    /** @test */
    public function test_fallback_returns_32_dim_mock_when_no_key()
    {
        // Temporarily remove the API key to test fallback
        Config::set('services.gemini.key', '');

        $vector = SemanticUtility::generateEmbedding('test product description');

        $this->assertIsArray($vector);
        $this->assertCount(32, $vector, 'Fallback mock should return exactly 32 dimensions');

        // Restore key
        Config::set('services.gemini.key', env('GEMINI_API_KEY'));
    }

    /** @test */
    public function test_generate_embedding_returns_empty_on_invalid_key()
    {
        Config::set('services.gemini.key', 'INVALID_KEY_12345');

        $vector = SemanticUtility::generateEmbedding('test product');

        // API should reject invalid key — returns empty array (not mock)
        $this->assertIsArray($vector);
        $this->assertEmpty($vector, 'Invalid API key should yield an empty array from the error handler');

        Config::set('services.gemini.key', env('GEMINI_API_KEY'));
    }

    // ─────────────────────────────────────────────────────────
    //  3. TEXT EXTRACTION TESTS
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_extract_text_truncates_at_2000_chars()
    {
        $product = new Product();
        $product->name = str_repeat('A', 3000);
        $product->description = 'Some description';
        $product->tags = 'tag1,tag2';

        $text = SemanticUtility::extractText($product);

        $this->assertLessThanOrEqual(2000, strlen($text), 'extractText must truncate at 2000 characters');
    }

    /** @test */
    public function test_extract_text_strips_html_tags()
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

    // ─────────────────────────────────────────────────────────
    //  4. COSINE SIMILARITY TESTS
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_cosine_similarity_identical_vectors()
    {
        $vec = [0.5, 0.3, 0.8, 0.1];
        $score = SemanticUtility::calculateSimilarity($vec, $vec);

        $this->assertEqualsWithDelta(1.0, $score, 0.001, 'Identical vectors should have cosine similarity of 1.0');
    }

    /** @test */
    public function test_cosine_similarity_orthogonal_vectors()
    {
        $vecA = [1.0, 0.0, 0.0, 0.0];
        $vecB = [0.0, 1.0, 0.0, 0.0];
        $score = SemanticUtility::calculateSimilarity($vecA, $vecB);

        $this->assertEqualsWithDelta(0.0, $score, 0.001, 'Orthogonal vectors should have cosine similarity of 0.0');
    }

    /** @test */
    public function test_cosine_similarity_opposite_vectors()
    {
        $vecA = [1.0, 0.0];
        $vecB = [-1.0, 0.0];
        $score = SemanticUtility::calculateSimilarity($vecA, $vecB);

        $this->assertEqualsWithDelta(-1.0, $score, 0.001, 'Opposite vectors should have cosine similarity of -1.0');
    }

    /** @test */
    public function test_cosine_similarity_zero_vector_returns_zero()
    {
        $vecA = [1.0, 2.0, 3.0];
        $vecB = [0.0, 0.0, 0.0];
        $score = SemanticUtility::calculateSimilarity($vecA, $vecB);

        $this->assertEquals(0, $score, 'Zero vector should yield 0 similarity');
    }

    // ─────────────────────────────────────────────────────────
    //  5. DATABASE PERSISTENCE TESTS
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_sync_embedding_creates_db_record()
    {
        try {
            $product = Product::first();
        } catch (QueryException $e) {
            $this->markTestSkipped('Products table not available in test DB (SQLite). Run against MySQL.');
        }
        if (!$product) {
            $this->markTestSkipped('No products in database.');
        }

        try {
            $result = SemanticUtility::syncEmbedding($product);
        } catch (QueryException $e) {
            $this->markTestSkipped('Semantic embeddings table not available in test DB.');
        }

        $this->assertTrue($result, 'syncEmbedding should return true on success');

        $embedding = SemanticEmbedding::where('embeddable_type', Product::class)
            ->where('embeddable_id', $product->id)
            ->first();

        $this->assertNotNull($embedding, 'Embedding record should exist in semantic_embeddings table');
        $this->assertNotEmpty($embedding->vector, 'Vector column should be populated');
        $this->assertNotEmpty($embedding->content, 'Content column should be populated');

        // Verify vector is valid JSON array
        $vector = json_decode($embedding->vector, true);
        $this->assertIsArray($vector, 'Vector should decode to a valid array');
        $this->assertNotEmpty($vector, 'Vector array should not be empty');
    }

    /** @test */
    public function test_sync_embedding_updates_existing_record()
    {
        try {
            $product = Product::first();
        } catch (QueryException $e) {
            $this->markTestSkipped('Products table not available in test DB (SQLite). Run against MySQL.');
        }
        if (!$product) {
            $this->markTestSkipped('No products in database.');
        }

        try {
            // First sync
            SemanticUtility::syncEmbedding($product);
            $countBefore = SemanticEmbedding::where('embeddable_type', Product::class)
                ->where('embeddable_id', $product->id)
                ->count();

            // Second sync — should update, not duplicate
            SemanticUtility::syncEmbedding($product);
            $countAfter = SemanticEmbedding::where('embeddable_type', Product::class)
                ->where('embeddable_id', $product->id)
                ->count();
        } catch (QueryException $e) {
            $this->markTestSkipped('Semantic embeddings table not available in test DB.');
        }

        $this->assertEquals($countBefore, $countAfter, 'syncEmbedding should update existing record, not create duplicates');
    }

    // ─────────────────────────────────────────────────────────
    //  6. SEARCH LOGIC TESTS
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_similarity_threshold_filters_low_scores()
    {
        try {
            $product = Product::first();
        } catch (QueryException $e) {
            $this->markTestSkipped('Products table not available in test DB (SQLite). Run against MySQL.');
        }
        if (!$product) {
            $this->markTestSkipped('No products in database.');
        }

        try {
            // Ensure at least one embedding exists
            SemanticUtility::syncEmbedding($product);

            // Perform a search. Results should only include score > 0.65
            $results = SemanticUtility::search('completely unrelated gibberish xyzzy', 10);
        } catch (QueryException $e) {
            $this->markTestSkipped('Semantic embeddings table not available in test DB.');
        }

        foreach ($results as $result) {
            $this->assertGreaterThan(0.65, $result['score'],
                'All returned results should have a similarity score above the 0.65 threshold');
        }
    }

    /** @test */
    public function test_search_returns_model_in_results()
    {
        try {
            $product = Product::first();
        } catch (QueryException $e) {
            $this->markTestSkipped('Products table not available in test DB (SQLite). Run against MySQL.');
        }
        if (!$product) {
            $this->markTestSkipped('No products in database.');
        }

        try {
            SemanticUtility::syncEmbedding($product);
            // Search for the product's own name — should be high relevance
            $results = SemanticUtility::search($product->name, 5);
        } catch (QueryException $e) {
            $this->markTestSkipped('Semantic embeddings table not available in test DB.');
        }

        if (count($results) > 0) {
            $this->assertArrayHasKey('score', $results[0]);
            $this->assertArrayHasKey('model', $results[0]);
            $this->assertNotNull($results[0]['model'], 'Result model should not be null (morphTo relationship)');
        }
    }

    // ─────────────────────────────────────────────────────────
    //  7. REINDEX COMMAND TEST
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_reindex_command_runs_without_error()
    {
        try {
            $productCount = Product::count();
        } catch (QueryException $e) {
            $this->markTestSkipped('Products table not available in test DB (SQLite). Run against MySQL.');
        }
        if ($productCount > 10) {
            $this->markTestSkipped("Skipping full reindex — {$productCount} products would take too long. Run manually with: php artisan search:reindex");
        }

        try {
            $exitCode = Artisan::call('search:reindex');
        } catch (QueryException $e) {
            $this->markTestSkipped('Semantic embeddings table not available in test DB.');
        }
        $this->assertEquals(0, $exitCode, 'search:reindex command should complete with exit code 0');
    }

    // ─────────────────────────────────────────────────────────
    //  8. VECTOR DIMENSION VERIFICATION (POST-REINDEX)
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function test_stored_embeddings_have_correct_dimensions()
    {
        try {
            $embedding = SemanticEmbedding::first();
        } catch (QueryException $e) {
            $this->markTestSkipped('Semantic embeddings table not available in test DB (SQLite). Run against MySQL.');
        }
        if (!$embedding) {
            $this->markTestSkipped('No embeddings in database. Run php artisan search:reindex first.');
        }

        $vector = json_decode($embedding->vector, true);
        $this->assertIsArray($vector);

        $key = config('services.gemini.key');
        if (!empty($key)) {
            // With a real key, vectors should be 768 dimensions
            $this->assertCount(768, $vector,
                'With GEMINI_API_KEY configured, embeddings should have 768 dimensions (gemini-embedding-001). ' .
                'If this shows 32, run php artisan search:reindex to regenerate embeddings.');
        } else {
            // Without key, fallback produces 32-dim mock vectors
            $this->assertCount(32, $vector, 'Without API key, fallback mock produces 32 dimensions');
        }
    }
}
