<?php

namespace Tests\Feature\Frontend;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SearchUxContractTest extends TestCase
{
    public function test_shared_search_layout_contains_safe_debounced_accessible_autocomplete_contract(): void
    {
        $source = File::get(resource_path('views/frontend/layouts/app.blade.php'));

        $this->assertStringContainsString('searchDebounceMs', $source);
        $this->assertStringContainsString('searchRequest.abort()', $source);
        $this->assertStringContainsString("$('<strong>').text(query)", $source);
        $this->assertStringContainsString("'aria-autocomplete': 'list'", $source);
        $this->assertStringContainsString("event.key === 'ArrowDown'", $source);
        $this->assertStringContainsString("event.key === 'Escape'", $source);
        $this->assertStringContainsString("name: 'mode'", $source);
        $this->assertStringContainsString('URLSearchParams(window.location.search)', $source);
    }

    public function test_product_listing_syncs_search_state_without_replacing_the_locale_path(): void
    {
        $source = File::get(resource_path('views/frontend/product_listing.blade.php'));

        $this->assertStringContainsString('function syncSearchUrl(formData)', $source);
        $this->assertStringContainsString("window.location.pathname + (queryString ? '?' + queryString : '')", $source);
        $this->assertStringContainsString('window.history.replaceState', $source);
        $this->assertStringContainsString('syncSearchUrl(formData);', $source);
    }
}
