<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class UserManager extends Component
{
    public string $search = '';

    public string $filterStatus = 'all';

    public string $filterCohort = 'all';

    public string $filterRole = 'student';

    public string $sortBy = 'latest';

    public ?int $inspectingUserId = null;

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    public function boot(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Administrator privileges required.');
        }
    }

    public function inspectUser(int $userId): void
    {
        $this->inspectingUserId = $userId;
    }

    public function closeInspectionModal(): void
    {
        $this->inspectingUserId = null;
    }

    public function toggleStatus(int $userId): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        if (Auth::id() === $userId) {
            $this->errorMessage = 'Security policy violation: Administrators cannot deactivate their own root account.';

            return;
        }

        $user = User::find($userId);
        if (! $user) {
            $this->errorMessage = 'User account not found.';

            return;
        }

        if ($user->isAdmin()) {
            $this->errorMessage = 'Security policy violation: Administrator status cannot be mutated from the student manager.';

            return;
        }

        if ($user->isActive()) {
            $user->deactivate();
            $this->feedbackMessage = "Account for student '{$user->name}' ({$user->email}) was deactivated. All active sessions were invalidated.";
        } else {
            $user->activate();
            $this->feedbackMessage = "Account for '{$user->name}' ({$user->email}) was reactivated.";
        }
    }

    public function resetStudentFinancialBaselines(int $userId): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $user = User::find($userId);

        if (! $user || ! $user->isStudent()) {
            $this->errorMessage = 'Target student account not found.';

            return;
        }

        $user->update([
            'monthly_allowance' => 0.00,
            'savings_goal' => 0.00,
        ]);

        $this->feedbackMessage = "Financial baselines (stipend and savings goal) for '{$user->name}' have been reset to zero.";
    }

    public function sendPasswordResetLink(int $userId): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $user = User::where('role', 'student')->find($userId);

        if (! $user) {
            $this->errorMessage = 'Target student account not found.';

            return;
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->feedbackMessage = "Password reset link sent to '{$user->email}'.";
        } else {
            $this->errorMessage = __($status);
        }
    }

    public function render()
    {
        $query = User::withCount(['transactions', 'budgets', 'categories', 'savingTips']);

        if ($this->filterRole === 'student') {
            $query->where('role', 'student');
        } elseif ($this->filterRole === 'admin') {
            $query->where('role', 'admin');
        }

        if ($this->filterStatus === 'active') {
            $query->where('status', 'active');
        } elseif ($this->filterStatus === 'disabled') {
            $query->where('status', 'disabled');
        }

        if ($this->filterCohort !== 'all') {
            $query->where('academic_year', $this->filterCohort);
        }

        if (filled($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($this->sortBy === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($this->sortBy === 'transactions_desc') {
            $query->orderBy('transactions_count', 'desc');
        } else {
            $query->latest();
        }

        $users = $query->get();

        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'active')->count();
        $disabledStudents = User::where('role', 'student')->where('status', 'disabled')->count();

        $inspectedUser = $this->inspectingUserId
            ? User::withCount(['transactions', 'budgets', 'savingTips', 'categoryLearnings'])->find($this->inspectingUserId)
            : null;

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'disabledStudents' => $disabledStudents,
            'inspectedUser' => $inspectedUser,
        ])->layout('components.layouts.admin', ['title' => 'Student Account Governance']);
    }
}
