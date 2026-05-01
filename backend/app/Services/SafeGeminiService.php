<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SafeGeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Generate content with maximum safety settings for kids.
     */
    public function generateSafeContent(string $prompt, string $systemInstruction = '')
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $prompt]]
                ]
            ],
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction ?: $this->getDefaultSystemInstruction()]]
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ]);

        if ($response->failed()) {
            Log::error('Gemini API request failed', ['response' => $response->body()]);
            return null;
        }

        $data = $response->json();

        // Check for safety blocks
        if (isset($data['promptFeedback']['blockReason'])) {
            Log::warning('Gemini blocked prompt due to safety', ['reason' => $data['promptFeedback']['blockReason']]);
            return 'I cannot answer that. Let\'s try something else!';
        }

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    protected function getDefaultSystemInstruction(): string
    {
        return "You are ChoreQuest AI, a helpful and friendly assistant for kids aged 5-12. " .
               "Always use simple language. Avoid any scary, violent, or inappropriate content. " .
               "Be encouraging and positive. Do not ask for or collect any personal information.";
    }
}
