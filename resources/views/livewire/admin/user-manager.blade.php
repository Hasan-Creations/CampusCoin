<div class="space-y-6">
    @if ($feedbackMessage)
        <div role="status" aria-live="polite" class="p-4 border border-[var(--accent)] bg-[var(--paper)] text-[var(--accent)] text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent)] shrink-0" />
                <span class="font-medium">{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon w-6 h-6 border-none">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div role="alert" aria-live="assertive" class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-[var(--expense)] text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-icon name="shield-alert" class="w-4 h-4 text-[var(--expense)] shrink-0" />
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon w-6 h-6 border-none">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-[var(--muted)] font-mono">Platform Governance</span>
            </div>
            <h1 class="font-display text-2xl sm:text-3xl font-medium tracking-tight text-[var(--ink)] mt-1 headline-rule">Student Account Governance</h1>
            <p class="text-xs sm:text-sm text-[var(--muted)] mt-1">Review student profiles, enforce account deactivations, inspect ledger activity, and reset baselines</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 border hairline-border bg-[var(--paper)] text-[var(--ink)] text-xs font-mono font-medium">
                <span class="w-2 h-2 rounded-full bg-[var(--accent)]"></span>
                ACTIVE AUTH GUARDS
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">Total Registered Students</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">{{ number_format($totalStudents) }}</div>
        </div>

        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--accent)]">Active Accounts</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--accent)] tabular-nums tracking-tight">{{ number_format($activeStudents) }}</div>
        </div>

        <div class="p-5 space-y-2 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--expense)]">Disabled / Deactivated</div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--expense)] tabular-nums tracking-tight">{{ number_format($disabledStudents) }}</div>
        </div>
    </div>

    <div class="card-campus p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="segmented-bar" role="group" aria-label="Account role filter">
                <button wire:click="$set('filterRole', 'student')" 
                        aria-label="Filter students only"
                        class="segmented-item {{ $filterRole === 'student' ? 'active' : '' }}">
                    Students
                </button>
                <button wire:click="$set('filterRole', 'admin')" 
                        aria-label="Filter administrators only"
                        class="segmented-item {{ $filterRole === 'admin' ? 'active' : '' }}">
                    Administrators
                </button>
                <button wire:click="$set('filterRole', 'all')" 
                        aria-label="Show all users"
                        class="segmented-item {{ $filterRole === 'all' ? 'active' : '' }}">
                    All Users
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterStatus" aria-label="Filter by account status" class="field !min-h-[38px] !py-1.5 !px-3 text-xs w-auto">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Accounts Only</option>
                    <option value="disabled">Disabled Accounts Only</option>
                </select>

                <select wire:model.live="filterCohort" aria-label="Filter by academic cohort" class="field !min-h-[38px] !py-1.5 !px-3 text-xs w-auto">
                    <option value="all">All Cohorts</option>
                    <option value="Freshman">Freshman</option>
                    <option value="Sophomore">Sophomore</option>
                    <option value="Junior">Junior</option>
                    <option value="Senior">Senior</option>
                    <option value="Graduate">Graduate</option>
                </select>

                <select wire:model.live="sortBy" aria-label="Sort users by" class="field !min-h-[38px] !py-1.5 !px-3 text-xs w-auto">
                    <option value="latest">Sort: Newest First</option>
                    <option value="name_asc">Sort: Name (A-Z)</option>
                    <option value="transactions_desc">Sort: Highest Activity</option>
                </select>

                <div class="relative min-w-[220px]">
                    <x-icon name="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)] pointer-events-none" />
                    <input type="text" 
                           wire:model.live.debounce.250ms="search" 
                           aria-label="Search students by name or email"
                           placeholder="Search name or email..." 
                           class="field !min-h-[38px] !py-1.5 !pl-9 !pr-3 text-xs w-full" />
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto border hairline-border">
        <table class="ledger-table w-full text-xs text-left">
            <thead>
                <tr>
                    <th scope="col">User & Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Cohort</th>
                    <th scope="col" class="text-right">Monthly Stipend</th>
                    <th scope="col" class="text-right">Savings Goal</th>
                    <th scope="col" class="text-center">Ledger Activity</th>
                    <th scope="col">Status</th>
                    <th scope="col">Registered</th>
                    <th scope="col" class="text-right">Administrative Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="table-row-tactile {{ ! $user->isActive() ? 'opacity-65' : '' }}">
                        <td>
                            <div class="font-medium text-[var(--ink)]">{{ $user->name }}</div>
                            <div class="font-mono text-[11px] text-[var(--muted)]">{{ $user->email }}</div>
                        </td>
                        <td>
                            <x-badge :variant="$user->isAdmin() ? 'secondary' : 'default'">
                                {{ ucfirst($user->role) }}
                            </x-badge>
                        </td>
                        <td class="font-mono text-[var(--muted)]">
                            {{ $user->academic_year ?? 'Staff / Root' }}
                        </td>
                        <td class="font-mono tabular-nums text-right text-[var(--ink)] font-medium">
                            ${{ number_format($user->monthly_allowance, 2) }}
                        </td>
                        <td class="font-mono tabular-nums text-right text-[var(--accent)] font-medium">
                            ${{ number_format($user->savings_goal, 2) }}
                        </td>
                        <td class="font-mono text-center text-[var(--muted)]">
                            <span title="Transactions" class="tabular-nums font-medium text-[var(--ink)]">{{ $user->transactions_count }}</span> tx / 
                            <span title="Budgets" class="tabular-nums">{{ $user->budgets_count }}</span> bgt
                        </td>
                        <td>
                            <x-badge :variant="$user->isActive() ? 'income' : 'expense'">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->isActive() ? 'bg-[var(--accent)]' : 'bg-[var(--expense)]' }}"></span>
                                <span>{{ $user->isActive() ? 'Active' : 'Disabled' }}</span>
                            </x-badge>
                        </td>
                        <td class="font-mono text-[var(--muted)]">
                            {{ $user->created_at->format('Y-m-d') }}
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <button wire:click="inspectUser({{ $user->id }})" 
                                        class="btn-icon !w-7 !h-7"
                                        title="Inspect Account Details"
                                        aria-label="Inspect account details for {{ $user->name }}">
                                    <x-icon name="eye" class="w-3.5 h-3.5" />
                                </button>

                                @if (! $user->isAdmin())
                                    <button wire:click="toggleStatus({{ $user->id }})" 
                                            wire:confirm="{{ $user->isActive() ? 'Are you sure you want to deactivate ' . $user->name . '? Their active login sessions will be immediately terminated.' : 'Reactivate account for ' . $user->name . '?' }}"
                                            aria-label="{{ $user->isActive() ? 'Deactivate student account for ' . $user->name : 'Reactivate student account for ' . $user->name }}"
                                            class="px-2.5 py-1 text-[11px] font-mono border hairline-border transition-colors {{ $user->isActive() ? 'text-[var(--expense)] border-[var(--hairline)] hover:border-[var(--expense)]' : 'text-[var(--accent)] border-[var(--hairline)] hover:border-[var(--accent)]' }}">
                                        {{ $user->isActive() ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-mono text-[var(--muted)] border hairline-border bg-[var(--paper)]">Root Protected</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-10 text-center text-xs text-[var(--muted)]">
                            No user accounts match the current filter or search criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($inspectedUser)
        <x-modal :show="true" maxWidth="lg" onClose="$wire.closeInspectionModal()" titleId="admin-inspect-user-title">
            <div class="flex items-center justify-between pb-4 border-b hairline-border">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 border hairline-border bg-[var(--paper)] flex items-center justify-center font-mono font-medium text-xs text-[var(--accent)]" aria-hidden="true">
                        #{{ $inspectedUser->id }}
                    </div>
                    <div>
                        <h3 id="admin-inspect-user-title" class="font-display text-lg font-medium text-[var(--ink)]">
                            {{ $inspectedUser->name }}
                        </h3>
                        <div class="text-xs font-mono text-[var(--muted)]">{{ $inspectedUser->email }}</div>
                    </div>
                </div>
                <button wire:click="closeInspectionModal" aria-label="Close inspection modal" class="btn-icon">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>

            <div class="space-y-5 text-xs py-4">
                <div class="grid grid-cols-2 gap-4 pb-4 border-b hairline-border">
                    <div>
                        <div class="text-[10px] font-caps text-[var(--muted)]">Account Role</div>
                        <div class="font-medium text-[var(--ink)] mt-0.5">{{ ucfirst($inspectedUser->role) }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-caps text-[var(--muted)]">Account Status</div>
                        <div class="mt-0.5">
                            @if($inspectedUser->isActive())
                                <span class="text-[var(--accent)] font-mono font-medium">Active</span>
                            @else
                                <span class="text-[var(--expense)] font-mono font-medium">Deactivated</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-caps text-[var(--muted)]">Cohort Standing</div>
                        <div class="font-mono text-[var(--ink)] mt-0.5">{{ $inspectedUser->academic_year ?? 'Unspecified' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-caps text-[var(--muted)]">Account Created</div>
                        <div class="font-mono text-[var(--muted)] mt-0.5">{{ $inspectedUser->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                <div class="space-y-3 pb-4 border-b hairline-border">
                    <div class="text-xs font-caps text-[var(--muted)]">Student Financial Baselines</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 border hairline-border bg-[var(--paper)]">
                            <div class="text-[10px] text-[var(--muted)]">Monthly Allowance / Inflow</div>
                            <div class="font-mono text-lg font-medium text-[var(--ink)] tabular-nums mt-0.5">
                                ${{ number_format($inspectedUser->monthly_allowance, 2) }}
                            </div>
                        </div>
                        <div class="p-3 border hairline-border bg-[var(--paper)]">
                            <div class="text-[10px] text-[var(--muted)]">Target Monthly Savings Goal</div>
                            <div class="font-mono text-lg font-medium text-[var(--accent)] tabular-nums mt-0.5">
                                ${{ number_format($inspectedUser->savings_goal, 2) }}
                            </div>
                        </div>
                    </div>
                    @if($inspectedUser->isStudent())
                        <div class="pt-2">
                            <button type="button" wire:click="sendPasswordResetLink({{ $inspectedUser->id }})" class="px-3 py-1.5 text-xs font-mono border hairline-border text-[var(--accent)] hover:border-[var(--accent)] transition-colors">
                                Send Password Reset Link
                            </button>
                            <button type="button" 
                                    wire:click="resetStudentFinancialBaselines({{ $inspectedUser->id }})"
                                    wire:confirm="Reset financial baselines (monthly allowance and savings target) to zero for this student?"
                                    class="px-3 py-1.5 text-xs font-mono border hairline-border text-[var(--secondary)] hover:border-[var(--secondary)] transition-colors">
                                Reset Financial Baselines to Zero
                            </button>
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="text-xs font-caps text-[var(--muted)]">Ledger & Engagement Telemetry</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                        <div class="p-2.5 bg-[var(--panel)]">
                            <div class="font-mono text-lg font-medium text-[var(--ink)] tabular-nums">{{ $inspectedUser->transactions_count }}</div>
                            <div class="text-[9px] font-caps text-[var(--muted)] mt-0.5">Transactions</div>
                        </div>
                        <div class="p-2.5 bg-[var(--panel)]">
                            <div class="font-mono text-lg font-medium text-[var(--ink)] tabular-nums">{{ $inspectedUser->budgets_count }}</div>
                            <div class="text-[9px] font-caps text-[var(--muted)] mt-0.5">Budgets</div>
                        </div>
                        <div class="p-2.5 bg-[var(--panel)]">
                            <div class="font-mono text-lg font-medium text-[var(--ink)] tabular-nums">{{ $inspectedUser->saving_tips_count }}</div>
                            <div class="text-[9px] font-caps text-[var(--muted)] mt-0.5">Tips</div>
                        </div>
                        <div class="p-2.5 bg-[var(--panel)]">
                            <div class="font-mono text-lg font-medium text-[var(--ink)] tabular-nums">{{ $inspectedUser->category_learnings_count }}</div>
                            <div class="text-[9px] font-caps text-[var(--muted)] mt-0.5">Learnings</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t hairline-border">
                <div>
                    @if(! $inspectedUser->isAdmin())
                        <button type="button" 
                                wire:click="toggleStatus({{ $inspectedUser->id }})"
                                wire:confirm="{{ $inspectedUser->isActive() ? 'Deactivate this student account and terminate their active sessions?' : 'Reactivate this student account?' }}"
                                class="px-3.5 py-2 text-xs font-mono font-medium border hairline-border transition-colors {{ $inspectedUser->isActive() ? 'text-[var(--expense)] border-[var(--hairline)] hover:border-[var(--expense)]' : 'text-[var(--accent)] border-[var(--hairline)] hover:border-[var(--accent)]' }}">
                            {{ $inspectedUser->isActive() ? 'Deactivate Account' : 'Reactivate Account' }}
                        </button>
                    @endif
                </div>
                <x-button variant="secondary" wire:click="closeInspectionModal">
                    Close
                </x-button>
            </div>
        </x-modal>
    @endif
</div>
