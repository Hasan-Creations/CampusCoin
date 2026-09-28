<?php

namespace App\Services\Categorization;

use App\Contracts\CategorizationProviderInterface;
use App\DTO\CategorySuggestion;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiCategorizationProvider implements CategorizationProviderInterface
{
    public function __construct(
        protected HeuristicCategorizationProvider $fallbackProvider,
        protected ?string $apiKey = null,
        protected string $model = 'gpt-4o-mini',
        protected int $timeoutSeconds = 3,
        protected string $baseUrl = 'https://api.openai.com/v1'
    ) {
        $this->apiKey = $apiKey ?: (config('services.openai.api_key') ?: env('AI_API_KEY', env('OPENAI_API_KEY')));
        $this->model = config('services.openai.model') ?: env('AI_MODEL', 'gpt-4o-mini');
        $this->baseUrl = config('services.openai.base_url') ?: 'https://api.openai.com/v1';
    }

    /**
     * @param  Collection<int, Category>  $availableCategories
     */
    public function suggestCategory(string $description, Collection $availableCategories, ?User $user = null): ?CategorySuggestion
    {
        $trimmed = trim($description);

        if (blank($trimmed) || $availableCategories->isEmpty()) {
            return null;
        }

        if (blank($this->apiKey)) {
            return $this->fallbackProvider->suggestCategory($trimmed, $availableCategories, $user);
        }

        try {
            $categoriesPayload = $availableCategories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
            ])->values()->all();

            $response = Http::timeout($this->timeoutSeconds)
                ->withToken($this->apiKey)
                ->acceptJson()
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert expense categorization assistant for college students. Given a transaction description and a list of available categories with IDs, choose the single most accurate category ID. Output valid JSON only with keys: "category_id" (integer), "confidence" (float between 0.0 and 1.0), and "explanation" (brief concise rationale string). Do not invent categories outside the provided list.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'transaction_description' => $trimmed,
                                'categories' => $categoriesPayload,
                            ], JSON_UNESCAPED_SLASHES),
                        ],
                    ],
                    'temperature' => 0.1,
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI categorization endpoint responded with error: '.$response->status());

                return $this->fallbackProvider->suggestCategory($trimmed, $availableCategories, $user);
            }

            $json = $response->json();
            $rawContent = $json['choices'][0]['message']['content'] ?? null;

            if (blank($rawContent)) {
                return $this->fallbackProvider->suggestCategory($trimmed, $availableCategories, $user);
            }

            // The response may wrap JSON in Markdown fences.
            $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawContent));
            $parsed = json_decode($cleanJson, true);

            if (! is_array($parsed) || ! isset($parsed['category_id'])) {
                Log::warning('OpenAI categorization returned invalid JSON structure.');

                return $this->fallbackProvider->suggestCategory($trimmed, $availableCategories, $user);
            }

            $suggestedId = (int) $parsed['category_id'];
            $targetCategory = $availableCategories->firstWhere('id', $suggestedId);

            // The model may return a category unavailable to this user.
            if (! $targetCategory) {
                Log::warning("OpenAI suggested non-existent or inaccessible category ID: {$suggestedId}");

                return $this->fallbackProvider->suggestCategory($trimmed, $availableCategories, $user);
            }

            $confidence = isset($parsed['confidence']) ? (float) $parsed['confidence'] : 0.85;
            $confidence = max(0.0, min(1.0, $confidence));

            $confidenceLevel = match (true) {
                $confidence >= 0.80 => 'high',
                $confidence >= 0.50 => 'medium',
                default => 'low',
            };

            $explanation = ! empty($parsed['explanation']) && is_string($parsed['explanation'])
                ? $parsed['explanation']
                : "AI predicted '{$targetCategory->name}' based on transaction description.";

            return new CategorySuggestion(
                categoryId: $targetCategory->id,
                categoryName: $targetCategory->name,
                confidence: $confidence,
                confidenceLevel: $confidenceLevel,
                explanation: $explanation,
                source: 'ai'
            );
        } catch (Throwable $e) {
            Log::warning('OpenAI categorization request failed or timed out: '.$e->getMessage());

            return $this->fallbackProvider->suggestCategory($trimmed, $availableCategories, $user);
        }
    }
}
