<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisualSearchController extends Controller
{
    /**
     * Handle the incoming visual search image upload, query the local Python AI service, 
     * and redirect to the standard search with the extracted keywords.
     */
    public function visualSearch(Request $request)
    {
        $request->validate([
            'visual_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $imageFile = $request->file('visual_image');

        try {
            // Forward the image to our local Python AI service (running on port 5001)
            $response = Http::attach(
                'image', 
                file_get_contents($imageFile->getRealPath()), 
                $imageFile->getClientOriginalName()
            )->timeout(30)->post('http://127.0.0.1:5001/predict');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success']) {
                    // Extract the AI-generated keywords
                    $keywords = $data['keywords'] ?? $data['caption'];
                    
                    // Flash a friendly message to the user showing what AI detected
                    flash(translate('Visual search detected: ') . '"' . $keywords . '"')->info();
                    
                    // Redirect to standard search page with the keywords
                    return redirect()->route('search', ['keyword' => $keywords]);
                }
            }
            
            Log::error('Visual Search AI failed to return success. Response: ' . $response->body());
            flash(translate('Visual search is currently unavailable. Please try typing your search instead.'))->warning();

        } catch (\Exception $e) {
            Log::error('Visual Search AI Service Exception: ' . $e->getMessage());
            flash(translate('Failed to connect to the Visual Search AI service. Is it running?'))->error();
        }

        return redirect()->back();
    }
}
