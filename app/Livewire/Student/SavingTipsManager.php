<?php

namespace App\Livewire\Student;

use App\Models\SavingTip;
use App\Services\SavingTipsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SavingTipsManager extends Component
{
    public string $activeTab = 'active';

    public ?string $feedbackMessage = null;

    protected $queryString = [
        'activeTab' => ['except' => 'active'],
    ];

    public function mount(SavingTipsService $service): void
    {
        $user = Auth::user();
        if ($user && SavingTip::where('user_id', $user->id)->count() === 0) {
            $service->syncTips($user);
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['active', 'pinned', 'dismissed'], true)) {
            $this->activeTab = $tab;
            $this->feedbackMessage = null;
        }
    }

    public function pinTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->pin();

        $this->feedbackMessage = 'Saved tip pinned to your bookmarks.';
    }

    public function unpinTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->unpin();

        $this->feedbackMessage = 'Tip unpinned and returned to active list.';
    }

    public function dismissTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->dismiss();

        $this->feedbackMessage = 'Tip dismissed. You can restore it anytime from the Dismissed tab.';
    }

    public function restoreTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->unDismiss();

        $this->feedbackMessage = 'Tip restored to active opportunities.';
    }

    public function refreshTips(SavingTipsService $service): void
    {
        $user = Auth::user();
        if ($user) {
            $service->syncTips($user);
            $this->feedbackMessage = 'Saving tips re-evaluated against your latest financial ledger.';
        }
    }

    public function render()
    {
        $uid = Auth::id();

        $activeCount = SavingTip::where('user_id', $uid)->active()->count();
        $pinnedCount = SavingTip::where('user_id', $uid)->pinned()->count();
        $dismissedCount = SavingTip::where('user_id', $uid)->dismissed()->count();

        $totalSavings = SavingTip::where('user_id', $uid)
            ->whereIn('status', ['active', 'pinned'])
            ->sum('estimated_savings');

        $tips = SavingTip::where('user_id', $uid)
            ->where('status', $this->activeTab)
            ->with('category')
            ->orderByDesc('estimated_savings')
            ->get();

        return view('livewire.student.saving-tips-manager', [
            'tips' => $tips,
            'activeCount' => $activeCount,
            'pinnedCount' => $pinnedCount,
            'dismissedCount' => $dismissedCount,
            'totalPotentialSavings' => (float) $totalSavings,
            'activeTab' => $this->activeTab,
        ])->layout('components.layouts.app', ['title' => 'Saving Tips']);
    }
}
