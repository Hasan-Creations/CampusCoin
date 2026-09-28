<?php

namespace App\Services;

use App\Contracts\CategorizationProviderInterface;
use App\DTO\CategorySuggestion;
use App\Models\Category;
use App\Models\CategoryLearning;
use App\Models\User;
use Illuminate\Support\Collection;

class AiCategorizationService
{
    public function __construct(
        protected CategorizationProviderInterface $provider
    ) {}

    public function suggestCategory(string $description, Collection $availableCategories, ?User $user = null): ?CategorySuggestion
    {
        $text = trim($description);

        if (blank($text) || $availableCategories->isEmpty()) {
            return null;
        }

        if ($user) {
            $learned = $this->findLearnedSuggestion($text, $availableCategories, $user->id);
            if ($learned) {
                return $learned;
            }
        }

        $suggestion = $this->provider->suggestCategory($text, $availableCategories, $user);
        if (! $suggestion) {
            return null;
        }

        $catMatch = $availableCategories->firstWhere('id', $suggestion->categoryId);
        if (! $catMatch) {
            return null;
        }

        return new CategorySuggestion(
            categoryId: $catMatch->id,
            categoryName: $catMatch->name,
            confidence: $suggestion->confidence,
            confidenceLevel: $suggestion->confidenceLevel,
            explanation: $suggestion->explanation,
            source: $suggestion->source
        );
    }

    public function recordCorrection(int $userId, string $description, int $categoryId): void
    {
        $keyword = mb_substr(trim(mb_strtolower($description)), 0, 100);

        if (mb_strlen($keyword) < 2) {
            return;
        }

        if (! Category::where('id', $categoryId)->exists()) {
            return;
        }

        $learning = CategoryLearning::where('user_id', $userId)
            ->where('keyword', $keyword)
            ->first();

        if ($learning) {
            $learning->category_id = $categoryId;
            $learning->usage_count = (int) $learning->usage_count + 1;
            $learning->last_used_at = now();
            $learning->save();
        } else {
            CategoryLearning::create([
                'user_id' => $userId,
                'keyword' => $keyword,
                'category_id' => $categoryId,
                'usage_count' => 1,
                'last_used_at' => now(),
            ]);
        }
    }

    protected function findLearnedSuggestion(string $description, Collection $availableCategories, int $userId): ?CategorySuggestion
    {
        $input = mb_strtolower(trim($description));

        $learnings = CategoryLearning::where('user_id', $userId)
            ->orderByDesc('usage_count')
            ->orderByDesc('last_used_at')
            ->get();

        if ($learnings->isEmpty()) {
            return null;
        }

        $matched = null;

        foreach ($learnings as $row) {
            if (mb_strtolower($row->keyword) === $input) {
                $matched = $row;
                break;
            }
        }

        if (! $matched) {
            foreach ($learnings as $row) {
                $kw = mb_strtolower($row->keyword);

                if (str_contains($kw, ' ') && str_contains($input, $kw)) {
                    $matched = $row;
                    break;
                }

                if (! str_contains($kw, ' ') && mb_strlen($kw) >= 3 && preg_match('/\b'.preg_quote($kw, '/').'\b/i', $input)) {
                    $matched = $row;
                    break;
                }
            }
        }

        if (! $matched) {
            return null;
        }

        $category = $availableCategories->firstWhere('id', $matched->category_id);
        if (! $category) {
            return null;
        }

        $confidence = min(0.99, 0.90 + ($matched->usage_count * 0.02));

        return new CategorySuggestion(
            categoryId: $category->id,
            categoryName: $category->name,
            confidence: $confidence,
            confidenceLevel: 'high',
            explanation: "Learned from your previous choice for '{$matched->keyword}'.",
            source: 'learned'
        );
    }
}
