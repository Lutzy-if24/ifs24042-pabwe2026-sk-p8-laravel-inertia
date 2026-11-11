<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->apiKey   = config('openai.api_key', '');
        $this->model    = config('openai.model', 'gpt-4o-mini');
        $this->baseUrl  = rtrim(config('openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->timeout  = (int) config('openai.timeout', 60);
    }

    /**
     * Generate blog post drafts via OpenAI Chat Completions API.
     *
     * @return GeneratedPostItem[]
     * @throws RuntimeException
     */
    public function generatePosts(int $count): array
    {
        $count = max(1, min(10, $count));
        $categories = implode(', ', PostCategory::values());

        $prompt = <<<PROMPT
Generate exactly {$count} blog post draft(s) in Bahasa Indonesia.

Return ONLY a valid JSON array (no markdown, no code fences, no explanation) with this structure:
[
  {
    "title": "...",
    "category": "one of: {$categories}",
    "excerpt": "1-2 sentence summary",
    "content": "Full markdown content, minimum 200 words"
  }
]

Requirements:
- Each post must have a unique, engaging title
- Category must be exactly one of: {$categories}
- Content must be in proper Markdown with headings, paragraphs, and lists
- Write in professional Indonesian language
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'       => $this->model,
                'messages'    => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.8,
            ]);

        if ($response->failed()) {
            $status = $response->status();
            $body   = $response->body();
            Log::error('OpenAI API request failed', ['status' => $status, 'body' => $body]);
            throw new RuntimeException("OpenAI API error (HTTP {$status}): {$body}");
        }

        $responseData = $response->json();
        $rawContent   = $responseData['choices'][0]['message']['content'] ?? '';

        return $this->parseJsonResponse($rawContent);
    }

    /**
     * Parse JSON from AI response, stripping code fences if present.
     *
     * @return GeneratedPostItem[]
     * @throws RuntimeException
     */
    private function parseJsonResponse(string $raw): array
    {
        // Strip markdown code fences
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $cleaned = preg_replace('/\s*```$/m', '', $cleaned);
        $cleaned = trim($cleaned);

        $decoded = json_decode($cleaned, true);

        if (!is_array($decoded)) {
            Log::error('OpenAI response is not valid JSON array', ['raw' => $raw]);
            throw new RuntimeException('AI returned invalid JSON. Please try again.');
        }

        return array_map(
            fn (array $item) => GeneratedPostItem::fromArray($item),
            $decoded
        );
    }
}
