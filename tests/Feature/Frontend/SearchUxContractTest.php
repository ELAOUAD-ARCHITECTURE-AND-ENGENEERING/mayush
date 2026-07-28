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
        $this->assertStringContainsString('searchUxV2Enabled', $source);
        $this->assertStringContainsString('if (!searchUxV2Enabled)', $source);
        $this->assertStringContainsString('searchRequest.abort()', $source);
        $this->assertStringContainsString("$('<strong>').text(query)", $source);
        $this->assertStringContainsString("'aria-autocomplete': 'list'", $source);
        $this->assertStringContainsString("event.key === 'ArrowDown'", $source);
        $this->assertStringContainsString("event.key === 'Escape'", $source);
        $this->assertStringContainsString("name: 'mode'", $source);
        $this->assertStringContainsString('URLSearchParams(window.location.search)', $source);
        $this->assertStringContainsString("!$(this).is('#search-form')", $source);
        $this->assertStringContainsString("listingToggle.toggleClass('active', enabled).attr('aria-pressed'", $source);
    }

    public function test_product_listing_syncs_search_state_without_replacing_the_locale_path(): void
    {
        $source = File::get(resource_path('views/frontend/product_listing.blade.php'));

        $this->assertStringContainsString('function syncSearchUrl(formData)', $source);
        $this->assertStringContainsString("window.location.pathname + (queryString ? '?' + queryString : '')", $source);
        $this->assertStringContainsString('window.history.replaceState', $source);
        $this->assertStringContainsString('syncSearchUrl(formData);', $source);

        $syncPosition = strpos($source, 'syncSearchUrl(formData);');
        $ajaxPosition = strpos($source, 'activeSearchRequest = $.ajax({');

        $this->assertNotFalse($syncPosition);
        $this->assertNotFalse($ajaxPosition);
        $this->assertLessThan($ajaxPosition, $syncPosition);

        $preorderPosition = strpos($source, "form_all_preorder_page === 'preorder_product'");
        $categoryPosition = strpos($source, 'category_page_first_time');

        $this->assertNotFalse($preorderPosition);
        $this->assertNotFalse($categoryPosition);
        $this->assertGreaterThan($preorderPosition, $syncPosition);
        $this->assertGreaterThan($categoryPosition, $syncPosition);
    }
}
