<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'status',
    'academic_year',
    'monthly_allowance',
    'savings_goal',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'monthly_allowance' => 'decimal:2',
            'savings_goal' => 'decimal:2',
        ];
    }

    /**
     * Determine whether the user is a system administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determine whether the user is a student.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Determine whether the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Deactivate this user account and terminate all active database sessions.
     */
    public function deactivate(): void
    {
        $this->update(['status' => 'disabled']);

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $this->id)->delete();
        }
    }

    /**
     * Reactivate this user account.
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Personal categories created by this student.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Financial transactions logged by this student.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Monthly budget goals set by this student.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Saving tips generated for this student.
     */
    public function savingTips(): HasMany
    {
        return $this->hasMany(SavingTip::class);
    }

    /**
     * Learned categorization preferences of this student.
     */
    public function categoryLearnings(): HasMany
    {
        return $this->hasMany(CategoryLearning::class);
    }
}
