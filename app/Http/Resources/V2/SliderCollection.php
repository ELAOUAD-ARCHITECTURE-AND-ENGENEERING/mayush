<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SliderCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'photo' => uploaded_asset($data['image']),
                    'url' => ($data['link']),
                    'title' => $data['title'] ?? null,
                    'description' => $data['description'] ?? null,
                    'cta_text' => $data['cta_text'] ?? null,
                    'cta_link' => $data['cta_link'] ?? null,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
