<?php

namespace App\Utility;

use App\Models\Search;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SearchUtility
{
    public static function store($query)
    {
        if ($query != null && $query != "") {
            if (!Schema::hasTable('searches')) {
                Log::warning('Search tracking skipped because the searches table is missing.');
                return;
            }

            $search = Search::where('query', $query)->first();
            if ($search != null) {
                $search->count = $search->count + 1;
                $search->save();
            } else {
                $search = new Search;
                $search->query = $query;
                $search->count = 1;
                $search->save();
            }

        }
    }
}
