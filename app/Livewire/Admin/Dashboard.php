<?php

namespace App\Livewire\Admin;

use App\Models\SystemTipTemplate;
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

        $user = User::find($userId);

        if (! $user) {
            $this->errorMessage = 'User account not found.';

            return;
        }

        if ($user->isAdmin()) {
            $this->errorMessage = 'Security policy violation: Administrator status cannot be toggled here.';

            return;
        }

        if ($user->isActive()) {
            $user->deactivate();
            $this->feedbackMessage = "Student account for '{$user->name}' ({$user->email}) has been deactivated. Active sessions terminated.";
        } else {
            $user->activate();
            $this->feedbackMessage = "Student account for '{$user->name}' ({$user->email}) has been reactivated.";
        }
    }

    public function render(AdminMetricsService $metricsService)
    {
        $metrics = $metricsService->getPlatformOverviewMetrics();
        $recentStudents = $metricsService->getRecentStudentAccounts(6);

        return view('livewire.admin.dashboard', [
            'metrics' => $metrics,
            'recentStudents' => $recentStudents,
            'systemTemplates' => SystemTipTemplate::where('is_active', true)->orderBy('type')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Operations & Metrics Telemetry']);
    }
}
