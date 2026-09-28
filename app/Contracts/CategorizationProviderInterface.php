<?php

namespace App\Contracts;

use App\DTO\CategorySuggestion;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

interface CategorizationProviderInterface
{
    /**
     * @param  Collection<int, Category>  $availableCategories
     */
    public function suggestCategory(string $description, Collection $availableCategories, ?User $user = null): ?CategorySuggestion;
}
