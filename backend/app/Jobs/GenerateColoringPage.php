<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Redemption;
use Illuminate\Support\Facades\Log;

class GenerateColoringPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Redemption $redemption)
    {
    }

    public function handle(): void
    {
        $prompt = $this->redemption->meta['prompt'] ?? 'cute animal';
        $fullPrompt = "Black and white coloring page for kids, thick lines, white background, no shading, " . $prompt;

        // Example using Stability AI
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.stability.key'),
            'Accept' => 'application/json',
        ])->post('https://api.stability.ai/v1/generation/stable-diffusion-v1-6/text-to-image', [
            'text_prompts' => [
                ['text' => $fullPrompt, 'weight' => 1]
            ],
            'cfg_scale' => 7,
            'height' => 512,
            'width' => 512,
            'samples' => 1,
            'steps' => 30,
        ]);

        if ($response->failed()) {
            Log::error('Stability AI request failed', ['response' => $response->body()]);
            $this->redemption->update(['status' => 'rejected', 'meta' => array_merge($this->redemption->meta, ['error' => 'Generation failed'])]);
            return;
        }

        $imageContent = base64_decode($response->json('artifacts.0.base64'));
        $filename = 'coloring_pages/' . $this->redemption->id . '_' . time() . '.png';

        Storage::disk('public')->put($filename, $imageContent);

        $this->redemption->update([
            'status' => 'completed',
            'meta' => array_merge($this->redemption->meta, ['image_url' => Storage::url($filename)])
        ]);
    }
}
