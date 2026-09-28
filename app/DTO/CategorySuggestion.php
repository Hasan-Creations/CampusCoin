<?php

namespace App\DTO;

class CategorySuggestion
{
    /**
     * Create a new category suggestion DTO instance.
     *
     * @param  int  $categoryId  The suggested category ID
     * @param  string  $categoryName  The name of the suggested category
     * @param  float  $confidence  Confidence score from 0.0 to 1.0
     * @param  string  $confidenceLevel  'high', 'medium', or 'low'
     * @param  string  $explanation  Brief rationale for the suggestion
     * @param  string  $source  Source of suggestion: 'learned', 'rules', or 'ai'
     */
    public function __construct(
        public int $categoryId,
        public string $categoryName,
        public float $confidence,
        public string $confidenceLevel,
        public string $explanation,
        public string $source
    ) {
        $this->confidence = max(0.0, min(1.0, $this->confidence));

        if (! in_array($this->confidenceLevel, ['high', 'medium', 'low'], true)) {
            $this->confidenceLevel = match (true) {
                $this->confidence >= 0.8 => 'high',
                $this->confidence >= 0.5 => 'medium',
                default => 'low',
            };
        }

        if (! in_array($this->source, ['learned', 'rules', 'ai'], true)) {
            $this->source = 'rules';
        }
    }

    /**
     * Convert the suggestion to an associative array for Livewire state and responses.
     *
     * @return array{
     *     categoryId: int,
     *     categoryName: string,
     *     confidence: float,
     *     confidenceLevel: string,
     *     explanation: string,
     *     source: string
     * }
     */
    public function toArray(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'confidence' => $this->confidence,
            'confidenceLevel' => $this->confidenceLevel,
            'explanation' => $this->explanation,
            'source' => $this->source,
        ];
    }

    /**
     * Create a suggestion from an array representation.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: (int) ($data['categoryId'] ?? 0),
            categoryName: (string) ($data['categoryName'] ?? ''),
            confidence: (float) ($data['confidence'] ?? 0.0),
            confidenceLevel: (string) ($data['confidenceLevel'] ?? 'low'),
            explanation: (string) ($data['explanation'] ?? ''),
            source: (string) ($data['source'] ?? 'rules')
        );
    }
}
