<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AITranslationService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    const DISCLAIMER = "\n\n[AI-generated translation. Contact school for official version.]";

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function translate(string $text, string $targetLanguage)
    {
        try {
            $prompt = "Translate the following text to {$targetLanguage}. Provide ONLY the translation. \n\n Text: {$text}";

            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $translated = $response->json('candidates.0.content.parts.0.text');
                return $translated . self::DISCLAIMER;
            }
        } catch (\Exception $e) {
            Log::error('Translation failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
