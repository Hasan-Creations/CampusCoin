<?php

namespace App\Contracts;

use App\DTO\CategorySuggestion;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

interface CategorizationProviderInterface
{
    /**
     * Suggest an appropriate category for a given transaction description.
     *
     * @param  string  $description  The raw or cleaned transaction description/merchant string.
     * @param  Collection<int, Category>  $availableCategories  The collection of categories accessible to the student.
     * @param  User|null  $user  The authenticated student requesting categorization.
     * @return CategorySuggestion|null The category suggestion DTO, or null if no confident category could be identified.
     */
    public function suggestCategory(string $description, Collection $availableCategories, ?User $user = null): ?CategorySuggestion;
}
