<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnnouncementSummarizerService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function summarize(string $announcement)
    {
        $prompt = "Summarize the following school announcement. Provide a bulleted list of key points and a one-sentence summary for a push notification. \n\n Announcement: {$announcement}";

        $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [['parts' => [['text' => $prompt]]]]
        ]);

        return $response->json('candidates.0.content.parts.0.text');
    }
}
