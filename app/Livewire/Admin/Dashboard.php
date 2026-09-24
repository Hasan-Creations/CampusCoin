<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\AdminMetricsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    public function boot(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Administrator privileges required.');
        }
    }

    public function toggleStudentStatus(int $userId, AdminMetricsService $metricsService): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $targetUser = User::find($userId);

        if (! $targetUser) {
            $this->errorMessage = 'User account not found.';

            return;
        }

        if ($targetUser->isAdmin()) {
            $this->errorMessage = 'Security policy violation: Administrator status cannot be toggled here.';

            return;
        }

        if ($targetUser->isActive()) {
            $targetUser->deactivate();
            $this->feedbackMessage = "Student account for '{$targetUser->name}' ({$targetUser->email}) has been deactivated. Active sessions terminated.";
        } else {
            $targetUser->activate();
            $this->feedbackMessage = "Student account for '{$targetUser->name}' ({$targetUser->email}) has been reactivated.";
        }
    }

    public function render(AdminMetricsService $metricsService)
    {
        $metrics = $metricsService->getPlatformOverviewMetrics();
        $recentStudents = $metricsService->getRecentStudentAccounts(6);

        return view('livewire.admin.dashboard', [
            'metrics' => $metrics,
            'recentStudents' => $recentStudents,
        ])->layout('components.layouts.admin', ['title' => 'Operations & Metrics Telemetry']);
    }
}
