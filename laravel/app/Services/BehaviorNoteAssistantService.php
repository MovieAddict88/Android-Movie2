<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BehaviorNoteAssistantService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function draftMessage(string $studentName, string $behaviorType)
    {
        $prompt = "Draft a professional and supportive message to a parent regarding their child, {$studentName}, who exhibited the following behavior: {$behaviorType}. Provide only the message content.";

        $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [['parts' => [['text' => $prompt]]]]
        ]);

        return $response->json('candidates.0.content.parts.0.text');
    }
}
