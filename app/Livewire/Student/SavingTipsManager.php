<?php

namespace App\Livewire\Student;

use App\Models\SavingTip;
use App\Services\SavingTipsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SavingTipsManager extends Component
{
    /**
     * Active tab filter: 'active', 'pinned', 'dismissed'.
     */
    public string $activeTab = 'active';

    /**
     * Flash feedback notification message.
     */
    public ?string $feedbackMessage = null;

    /**
     * Query string persistence for tab navigation.
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'activeTab' => ['except' => 'active'],
    ];

    /**
     * Mount component and evaluate current tips.
     */
    public function mount(SavingTipsService $service): void
    {
        $user = Auth::user();
        if ($user && SavingTip::where('user_id', $user->id)->count() === 0) {
            $service->syncTips($user);
        }
    }

    /**
     * Switch the current tab.
     */
    public function setTab(string $tab): void
    {
        if (in_array($tab, ['active', 'pinned', 'dismissed'], true)) {
            $this->activeTab = $tab;
            $this->feedbackMessage = null;
        }
    }

    /**
     * Pin a saving tip for long-term reference.
     */
    public function pinTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->pin();

        $this->feedbackMessage = 'Saved tip pinned to your bookmarks.';
    }

    /**
     * Unpin a saving tip back to active opportunities.
     */
    public function unpinTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->unpin();

        $this->feedbackMessage = 'Tip unpinned and returned to active list.';
    }

    /**
     * Dismiss a saving tip.
     */
    public function dismissTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->dismiss();

        $this->feedbackMessage = 'Tip dismissed. You can restore it anytime from the Dismissed tab.';
    }

    /**
     * Restore a dismissed saving tip back to active.
     */
    public function restoreTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->unDismiss();

        $this->feedbackMessage = 'Tip restored to active opportunities.';
    }

    /**
     * Re-evaluate deterministic rules manually.
     */
    public function refreshTips(SavingTipsService $service): void
    {
        $user = Auth::user();
        if ($user) {
            $service->syncTips($user);
            $this->feedbackMessage = 'Saving tips re-evaluated against your latest financial ledger.';
        }
    }

    /**
     * Render the saving tips manager interface.
     */
    public function render()
    {
        $userId = Auth::id();

        $activeCount = SavingTip::where('user_id', $userId)->active()->count();
        $pinnedCount = SavingTip::where('user_id', $userId)->pinned()->count();
        $dismissedCount = SavingTip::where('user_id', $userId)->dismissed()->count();

        $totalPotentialSavings = SavingTip::where('user_id', $userId)
            ->whereIn('status', ['active', 'pinned'])
            ->sum('estimated_savings');

        $tips = SavingTip::where('user_id', $userId)
            ->where('status', $this->activeTab)
            ->with('category')
            ->orderByDesc('estimated_savings')
            ->get();

        return view('livewire.student.saving-tips-manager', [
            'tips' => $tips,
            'activeCount' => $activeCount,
            'pinnedCount' => $pinnedCount,
            'dismissedCount' => $dismissedCount,
            'totalPotentialSavings' => (float) $totalPotentialSavings,
            'activeTab' => $this->activeTab,
        ])->layout('components.layouts.app', ['title' => 'Saving Tips']);
    }
}
