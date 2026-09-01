<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function get_page_data(Request $request)
    {
        $page_name = $request->page;
        $page = Page::where('slug', $page_name)->first();
        if (!$page) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Page introuvable'
            ], 404);
        }
        return new PageResource($page);
    }

    public function show($slug)
    {
        $page = Page::where('slug', $slug)->first();
        if (!$page) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Page introuvable'
            ], 404);
        }
        return new PageResource($page);
    }

    public function useful_links()
    {
        $pages = Page::where('type', '!=', 'home_page')
            ->select('id', 'type', 'title', 'slug')
            ->get()
            ->map(function ($page) {
                return [
                    'id' => (string) $page->id,
                    'type' => $page->type,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'url' => 'https://mayushdesign.com/' . $page->slug,
                ];
            });

        return response()->json([
            'data' => $pages,
            'success' => true,
            'status' => 200,
        ]);
    }
}

