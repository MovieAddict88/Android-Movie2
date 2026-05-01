<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOnboardingService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct(?string $tenantApiKey = null)
    {
        // Use tenant-specific key if provided, otherwise fallback to master key
        $this->apiKey = $tenantApiKey ?? config('services.gemini.key');
    }

    /**
     * Parse a job description and generate a personalized 30/60/90 day onboarding task list.
     */
    public function generateOnboardingTasks(string $jobDescription): array
    {
        $prompt = "As an HR expert, analyze the following job description and generate a structured 30/60/90 day onboarding plan.
        The output must be a JSON array of tasks, each with: 'title', 'description', 'day_target' (30, 60, or 90), and 'category' (Compliance, Training, Integration).

        Job Description:
        {$jobDescription}";

        try {
            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return json_decode($result['candidates'][0]['content']['parts'][0]['text'], true) ?? [];
            }

            Log::error('Gemini API Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Analyze uploaded documents (IDs, certificates) for validity.
     */
    public function analyzeDocument(string $base64File, string $mimeType): array
    {
        $prompt = "Analyze this document image. Is it a valid identification or certification document?
        Extract the expiry date and status. Return JSON: {'is_valid': bool, 'expiry_date': 'YYYY-MM-DD', 'document_type': 'string', 'confidence_score': float}";

        try {
            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64File
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return json_decode($result['candidates'][0]['content']['parts'][0]['text'], true) ?? [];
            }

            return ['is_valid' => false, 'error' => 'API failure'];
        } catch (\Exception $e) {
            return ['is_valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract data from document (auto-fill feature).
     */
    public function extractDocumentData(string $base64File, string $mimeType): array
    {
        $prompt = "Extract all relevant fields from this document (e.g., Name, Date of Birth, ID Number, Issue Date).
        Return as a flat JSON key-value object.";

        try {
            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64File
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return json_decode($result['candidates'][0]['content']['parts'][0]['text'], true) ?? [];
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
