<?php

namespace App\Services\Categorization;

use App\Contracts\CategorizationProviderInterface;
use App\DTO\CategorySuggestion;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

class HeuristicCategorizationProvider implements CategorizationProviderInterface
{
    /**
     * Semantic keyword definitions mapping student expenses to category concepts.
     *
     * @var array<string, array{keywords: list<string>, category_aliases: list<string>}>
     */
    protected array $rules = [
        'food' => [
            'keywords' => [
                'uber eats', 'doordash', 'grubhub', 'tim hortons', 'trader joe', 'whole foods',
                'starbucks', 'dunkin', 'mcdonalds', 'burger king', 'subway', 'chipotle', 'wendys',
                'panera', 'dominos', 'pizza', 'burger', 'cafe', 'cafeteria', 'canteen', 'meal',
                'lunch', 'dinner', 'breakfast', 'coffee', 'groceries', 'grocery', 'supermarket',
                'snacks', 'boba', 'drink', 'drinks', 'food', 'taco', 'sushi', 'bakery', 'restaurant',
                'dining', 'kfc', 'eats', 'kroger', 'aldi', 'costco', 'market',
            ],
            'category_aliases' => ['food', 'groceries', 'dining', 'eating'],
        ],
        'academics' => [
            'keywords' => [
                'campus store', 'tuition fee', 'course material', 'chegg', 'quizlet', 'coursehero',
                'udemy', 'coursera', 'textbook', 'textbooks', 'tuition', 'bookstore', 'stationery',
                'supplies', 'books', 'book', 'exam', 'printing', 'print', 'notebook', 'notebooks',
                'paper', 'pen', 'pens', 'library', 'lab fees', 'lab fee', 'course', 'courses', 'canvas',
            ],
            'category_aliases' => ['academics', 'books', 'education', 'academic', 'studies', 'school'],
        ],
        'transport' => [
            'keywords' => [
                'uber', 'lyft', 'subway', 'metro', 'bus fare', 'train ticket', 'transit', 'shuttle',
                'commute', 'amtrak', 'trainline', 'parking', 'gas station', 'shell', 'chevron', 'bp',
                'petrol', 'fuel', 'bus', 'train', 'taxi', 'toll', 'scooter', 'lime', 'bird', 'flight',
            ],
            'category_aliases' => ['transport', 'commute', 'travel', 'transportation'],
        ],
        'housing' => [
            'keywords' => [
                'hostel fee', 'dorm fee', 'room rent', 'monthly rent', 'apartment lease', 'housing deposit',
                'hostel', 'dorm', 'dormitory', 'rent', 'housing', 'landlord', 'lease', 'roommate', 'flat',
            ],
            'category_aliases' => ['hostel/rent', 'rent', 'hostel', 'housing', 'dorm'],
        ],
        'utilities' => [
            'keywords' => [
                'water bill', 'electricity bill', 'gas bill', 'internet bill', 'wifi bill', 'broadband',
                'electric', 'electricity', 'utilities', 'utility', 'wifi', 'internet', 'power bill',
                'comcast', 'xfinity', 'at&t', 'verizon', 't-mobile',
            ],
            'category_aliases' => ['utilities', 'utility', 'hostel/rent', 'housing', 'bills'],
        ],
        'subscriptions' => [
            'keywords' => [
                'amazon prime', 'apple music', 'prime video', 'youtube premium', 'chatgpt plus',
                'netflix', 'spotify', 'youtube', 'hulu', 'disney', 'patreon', 'icloud', 'audible',
                'subscription', 'subscriptions', 'gym membership', 'github', 'fitness',
            ],
            'category_aliases' => ['subscriptions', 'subscription', 'digital', 'entertainment'],
        ],
        'tech' => [
            'keywords' => [
                'apple store', 'best buy', 'macbook', 'laptop', 'computer', 'ipad', 'iphone', 'dell',
                'lenovo', 'headphones', 'hardware', 'electronics', 'monitor', 'keyboard', 'gadgets', 'tech',
            ],
            'category_aliases' => ['tech', 'electronics', 'technology', 'academics', 'miscellaneous'],
        ],
        'entertainment' => [
            'keywords' => [
                'steam games', 'movie ticket', 'cinema ticket', 'amc', 'regal', 'playstation', 'xbox',
                'nintendo', 'concert', 'festival', 'theatre', 'theater', 'bowling', 'arcade', 'billiards',
                'gaming', 'game', 'party', 'cinema', 'movie', 'movies', 'club', 'recreation',
            ],
            'category_aliases' => ['entertainment', 'recreation', 'leisure', 'fun'],
        ],
        'miscellaneous' => [
            'keywords' => [
                'pharmacy', 'prescription', 'cvs', 'walgreens', 'doctor', 'clinic', 'dentist', 'haircut',
                'barber', 'salon', 'laundry', 'dry cleaner', 'laundromat', 'atm fee', 'medical', 'misc',
                'miscellaneous', 'personal care',
            ],
            'category_aliases' => ['miscellaneous', 'misc', 'health', 'personal', 'other'],
        ],
        'allowance' => [
            'keywords' => ['monthly allowance', 'pocket money', 'from parents', 'parent transfer', 'allowance'],
            'category_aliases' => ['allowance'],
        ],
        'job' => [
            'keywords' => ['part-time job', 'campus work', 'work study', 'payroll', 'paycheck', 'salary', 'wages', 'stipend'],
            'category_aliases' => ['part-time job', 'job', 'salary', 'other income'],
        ],
        'scholarship' => [
            'keywords' => ['scholarship grant', 'merit scholarship', 'tuition grant', 'financial aid', 'fellowship', 'scholarship', 'grant'],
            'category_aliases' => ['scholarship'],
        ],
        'gift' => [
            'keywords' => ['birthday gift', 'holiday gift', 'gift received', 'gift money', 'gift'],
            'category_aliases' => ['gift', 'other income'],
        ],
    ];

    /**
     * Suggest a category using deterministic heuristic pattern matching.
     *
     * @param  Collection<int, Category>  $availableCategories
     */
    public function suggestCategory(string $description, Collection $availableCategories, ?User $user = null): ?CategorySuggestion
    {
        $normalized = mb_strtolower(trim($description));

        if (blank($normalized) || $availableCategories->isEmpty()) {
            return null;
        }

        // Direct category name match
        foreach ($availableCategories as $category) {
            $catName = mb_strtolower(trim($category->name));
            if (mb_strlen($catName) >= 3 && $this->matchesText($normalized, $catName)) {
                return new CategorySuggestion(
                    categoryId: $category->id,
                    categoryName: $category->name,
                    confidence: 0.90,
                    confidenceLevel: 'high',
                    explanation: "Directly matched category name '{$category->name}' in transaction description.",
                    source: 'rules'
                );
            }
        }

        // Match semantic keywords
        foreach ($this->rules as $group => $data) {
            foreach ($data['keywords'] as $keyword) {
                if ($this->matchesText($normalized, $keyword)) {
                    $matchedCategory = $this->findCategoryForAliases($availableCategories, $data['category_aliases']);

                    if ($matchedCategory) {
                        $isMultiWord = str_contains($keyword, ' ');
                        $confidence = $isMultiWord ? 0.85 : 0.75;
                        $confidenceLevel = $confidence >= 0.80 ? 'high' : 'medium';

                        return new CategorySuggestion(
                            categoryId: $matchedCategory->id,
                            categoryName: $matchedCategory->name,
                            confidence: $confidence,
                            confidenceLevel: $confidenceLevel,
                            explanation: "Matched keyword '{$keyword}' to category '{$matchedCategory->name}'.",
                            source: 'rules'
                        );
                    }
                }
            }
        }

        return null;
    }

    protected function matchesText(string $text, string $keyword): bool
    {
        if (str_contains($keyword, ' ')) {
            return str_contains($text, $keyword);
        }

        // Single word boundary check
        return (bool) preg_match('/\b'.preg_quote($keyword, '/').'\b/i', $text);
    }

    protected function findCategoryForAliases(Collection $categories, array $aliases): ?Category
    {
        // Exact match
        foreach ($aliases as $alias) {
            $cat = $categories->first(fn (Category $c) => mb_strtolower($c->name) === $alias);
            if ($cat) {
                return $cat;
            }
        }

        // Partial match
        foreach ($aliases as $alias) {
            $cat = $categories->first(function (Category $c) use ($alias) {
                $name = mb_strtolower($c->name);

                return str_contains($name, $alias) || str_contains($alias, $name);
            });
            if ($cat) {
                return $cat;
            }
        }

        return null;
    }
}
