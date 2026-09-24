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
    /**
     * Create a new central AI categorization service.
     *
     * @param  CategorizationProviderInterface  $provider  The configured suggestion provider (OpenAI or Heuristic)
     */
    public function __construct(
        protected CategorizationProviderInterface $provider
    ) {}

    /**
     * Suggest an expense category for a student transaction.
     * Checks user-specific learned corrections first, falls back to the configured provider,
     * and strictly validates that the suggested category belongs to the student's available categories.
     *
     * @param  string  $description  Transaction note, merchant, or memo
     * @param  Collection<int, Category>  $availableCategories  Categories accessible to this student
     * @param  User|null  $user  Authenticated student
     */
    public function suggestCategory(string $description, Collection $availableCategories, ?User $user = null): ?CategorySuggestion
    {
        $trimmed = trim($description);

        if (blank($trimmed) || $availableCategories->isEmpty()) {
            return null;
        }

        // 1. Check Student's Learned Corrections First (Tenant-Isolated)
        if ($user) {
            $learnedSuggestion = $this->findLearnedSuggestion($trimmed, $availableCategories, $user->id);
            if ($learnedSuggestion) {
                return $learnedSuggestion;
            }
        }

        // 2. Query Configured Categorization Provider (AI or Heuristic)
        $suggestion = $this->provider->suggestCategory($trimmed, $availableCategories, $user);

        if (! $suggestion) {
            return null;
        }

        // 3. Strict Safety Validation: Never return a non-existent or inaccessible category
        $validCategory = $availableCategories->firstWhere('id', $suggestion->categoryId);

        if (! $validCategory) {
            return null;
        }

        // Ensure category name is synchronized with authoritative model
        return new CategorySuggestion(
            categoryId: $validCategory->id,
            categoryName: $validCategory->name,
            confidence: $suggestion->confidence,
            confidenceLevel: $suggestion->confidenceLevel,
            explanation: $suggestion->explanation,
            source: $suggestion->source
        );
    }

    /**
     * Record or update student-specific categorization learning.
     * Strictly student-isolated: Student A's corrections NEVER affect Student B.
     */
    public function recordCorrection(int $userId, string $description, int $categoryId): void
    {
        $keyword = mb_substr(trim(mb_strtolower($description)), 0, 100);

        if (mb_strlen($keyword) < 2) {
            return;
        }

        // Verify target category exists in the system
        $categoryExists = Category::where('id', $categoryId)->exists();
        if (! $categoryExists) {
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

    /**
     * Look up learned category mappings for a student.
     *
     * @param  Collection<int, Category>  $availableCategories
     */
    protected function findLearnedSuggestion(string $description, Collection $availableCategories, int $userId): ?CategorySuggestion
    {
        $needle = mb_strtolower(trim($description));

        // Fetch learnings for this student, prioritized by usage count and recency
        $learnings = CategoryLearning::where('user_id', $userId)
            ->orderByDesc('usage_count')
            ->orderByDesc('last_used_at')
            ->get();

        if ($learnings->isEmpty()) {
            return null;
        }

        $matched = null;

        // Pass 1: Exact keyword match
        foreach ($learnings as $learning) {
            if (mb_strtolower($learning->keyword) === $needle) {
                $matched = $learning;
                break;
            }
        }

        // Pass 2: Token / phrase containment match
        if (! $matched) {
            foreach ($learnings as $learning) {
                $kw = mb_strtolower($learning->keyword);

                if (str_contains($kw, ' ') && str_contains($needle, $kw)) {
                    $matched = $learning;
                    break;
                }

                if (! str_contains($kw, ' ') && mb_strlen($kw) >= 3 && preg_match('/\b'.preg_quote($kw, '/').'\b/i', $needle)) {
                    $matched = $learning;
                    break;
                }
            }
        }

        if (! $matched) {
            return null;
        }

        // Check if the learned category is still accessible to the student
        $category = $availableCategories->firstWhere('id', $matched->category_id);
        if (! $category) {
            return null;
        }

        // Higher usage count yields higher confidence (0.90 to 0.99)
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
