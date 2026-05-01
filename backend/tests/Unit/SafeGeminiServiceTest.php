<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SafeGeminiService;
use Illuminate\Support\Facades\Http;

class SafeGeminiServiceTest extends TestCase
{
    public function test_it_applies_safety_settings()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Friendly response']]]]
                ]
            ], 200)
        ]);

        $service = new SafeGeminiService();
        $response = $service->generateSafeContent('Hello');

        $this->assertEquals('Friendly response', $response);

        Http::assertSent(function ($request) {
            $body = $request->json();
            return isset($body['safetySettings']) &&
                   count($body['safetySettings']) === 4 &&
                   $body['safetySettings'][0]['threshold'] === 'BLOCK_LOW_AND_ABOVE';
        });
    }

    public function test_it_handles_safety_blocks()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'promptFeedback' => ['blockReason' => 'SAFETY']
            ], 200)
        ]);

        $service = new SafeGeminiService();
        $response = $service->generateSafeContent('Bad prompt');

        $this->assertEquals('I cannot answer that. Let\'s try something else!', $response);
    }
}
