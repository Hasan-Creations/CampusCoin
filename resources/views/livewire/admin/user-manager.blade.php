<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if ($feedbackMessage)
        <div role="status" aria-live="polite" class="p-4 rounded-[10px] border hairline-border bg-[var(--success-tint)] text-[var(--success)] text-xs flex items-center justify-between shadow-tactile-sm">
            <div class="flex items-center gap-2.5">
                <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--success)] shrink-0" />
                <span class="font-medium">{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon !w-6 !h-6 text-[var(--success)] hover:bg-[var(--success-hover)] hover:text-white transition-colors">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div role="alert" aria-live="assertive" class="p-4 rounded-[10px] border hairline-border bg-[var(--danger-tint)] text-[var(--danger)] text-xs flex items-center justify-between shadow-tactile-sm">
            <div class="flex items-center gap-2.5">
                <x-icon name="shield-alert" class="w-4 h-4 text-[var(--danger)] shrink-0" />
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="btn-icon !w-6 !h-6 text-[var(--danger)] hover:bg-[var(--danger-hover)] hover:text-white transition-colors">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-[var(--text-muted)] font-mono">Platform Governance</span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-bold tracking-tight text-[var(--text-primary)] mt-1">Student Account Governance</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">Review student profiles, enforce account deactivations, inspect ledger activity, and reset baselines</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-[var(--text-primary)] text-xs font-mono font-medium">
                <span class="w-2 h-2 rounded-full bg-[var(--success)]"></span>
                ACTIVE AUTH GUARDS
            </span>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card-campus p-5 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Total Registered Students</div>
                <div class="font-mono text-3xl font-bold text-[var(--text-primary)] tabular-nums mt-1">{{ number_format($totalStudents) }}</div>
            </div>
            <div class="w-10 h-10 rounded-[8px] bg-[var(--bg-subtle)] flex items-center justify-center text-[var(--accent-primary)]">
                <x-icon name="users" class="w-5 h-5" />
            </div>
        </div>

        <div class="card-campus p-5 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--success)]">Active Accounts</div>
                <div class="font-mono text-3xl font-bold text-[var(--success)] tabular-nums mt-1">{{ number_format($activeStudents) }}</div>
            </div>
            <div class="w-10 h-10 rounded-[8px] bg-[var(--success-tint)] flex items-center justify-center text-[var(--success)]">
                <x-icon name="user-check" class="w-5 h-5" />
            </div>
        </div>

        <div class="card-campus p-5 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--danger)]">Disabled / Deactivated</div>
                <div class="font-mono text-3xl font-bold text-[var(--danger)] tabular-nums mt-1">{{ number_format($disabledStudents) }}</div>
            </div>
            <div class="w-10 h-10 rounded-[8px] bg-[var(--danger-tint)] flex items-center justify-center text-[var(--danger)]">
                <x-icon name="user-x" class="w-5 h-5" />
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card-campus p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Role Selector -->
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

            <!-- Controls: Status, Cohort, Sort, Search -->
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterStatus" aria-label="Filter by account status" class="input-campus !min-h-[38px] !py-1.5 !px-3 text-xs">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Accounts Only</option>
                    <option value="disabled">Disabled Accounts Only</option>
                </select>

                <select wire:model.live="filterCohort" aria-label="Filter by academic cohort" class="input-campus !min-h-[38px] !py-1.5 !px-3 text-xs">
                    <option value="all">All Cohorts</option>
                    <option value="Freshman">Freshman</option>
                    <option value="Sophomore">Sophomore</option>
                    <option value="Junior">Junior</option>
                    <option value="Senior">Senior</option>
                    <option value="Graduate">Graduate</option>
                </select>

                <select wire:model.live="sortBy" aria-label="Sort users by" class="input-campus !min-h-[38px] !py-1.5 !px-3 text-xs">
                    <option value="latest">Sort: Newest First</option>
                    <option value="name_asc">Sort: Name (A-Z)</option>
                    <option value="transactions_desc">Sort: Highest Activity</option>
                </select>

                <div class="relative min-w-[220px]">
                    <x-icon name="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                    <input type="text" 
                           wire:model.live.debounce.250ms="search" 
                           aria-label="Search students by name or email"
                           placeholder="Search name or email..." 
                           class="input-campus !min-h-[38px] !py-1.5 !pl-9 !pr-3 text-xs w-full" />
                </div>
            </div>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="card-campus p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)] border-b hairline-border">
                    <tr>
                        <th scope="col" class="p-3.5">User & Email</th>
                        <th scope="col" class="p-3.5">Role</th>
                        <th scope="col" class="p-3.5">Cohort</th>
                        <th scope="col" class="p-3.5 text-right">Monthly Stipend</th>
                        <th scope="col" class="p-3.5 text-right">Savings Goal</th>
                        <th scope="col" class="p-3.5 text-center">Ledger Activity</th>
                        <th scope="col" class="p-3.5">Status</th>
                        <th scope="col" class="p-3.5">Registered</th>
                        <th scope="col" class="p-3.5 text-right">Administrative Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y hairline-border bg-[var(--bg-surface)]">
                    @forelse($users as $user)
                        <tr class="table-row-tactile {{ ! $user->isActive() ? 'opacity-65' : '' }}">
                            <td class="p-3.5">
                                <div class="font-medium text-[var(--text-primary)]">{{ $user->name }}</div>
                                <div class="font-mono text-[11px] text-[var(--text-muted)]">{{ $user->email }}</div>
                            </td>
                            <td class="p-3.5">
                                <span class="badge-campus {{ $user->isAdmin() ? 'bg-[var(--gold-tint)] text-[var(--gold-hover)] border hairline-border' : 'bg-[var(--bg-subtle)] text-[var(--accent-primary)] border hairline-border' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono text-[var(--text-muted)]">
                                {{ $user->academic_year ?? 'Staff / Root' }}
                            </td>
                            <td class="p-3.5 font-mono tabular-nums text-right text-[var(--text-primary)] font-semibold">
                                ${{ number_format($user->monthly_allowance, 2) }}
                            </td>
                            <td class="p-3.5 font-mono tabular-nums text-right text-[var(--success)] font-semibold">
                                ${{ number_format($user->savings_goal, 2) }}
                            </td>
                            <td class="p-3.5 font-mono text-center text-[var(--text-muted)]">
                                <span title="Transactions" class="tabular-nums font-semibold text-[var(--text-primary)]">{{ $user->transactions_count }}</span> tx / 
                                <span title="Budgets" class="tabular-nums">{{ $user->budgets_count }}</span> bgt
                            </td>
                            <td class="p-3.5">
                                @if($user->isActive())
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--success-tint)] text-[var(--success)] font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--success)]"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--danger-tint)] text-[var(--danger)] font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--danger)]"></span>
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono text-[var(--text-muted)]">
                                {{ $user->created_at->format('Y-m-d') }}
                            </td>
                            <td class="p-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Inspect Button -->
                                    <button wire:click="inspectUser({{ $user->id }})" 
                                            class="btn-icon !w-7 !h-7"
                                            title="Inspect Account Details">
                                        <x-icon name="eye" class="w-3.5 h-3.5" />
                                    </button>

                                    <!-- Status Toggle Button -->
                                    @if (! $user->isAdmin())
                                        <button wire:click="toggleStatus({{ $user->id }})" 
                                                wire:confirm="{{ $user->isActive() ? 'Are you sure you want to deactivate ' . $user->name . '? Their active login sessions will be immediately terminated.' : 'Reactivate account for ' . $user->name . '?' }}"
                                                class="px-2.5 py-1 rounded-[6px] text-[11px] font-mono border hairline-border transition-colors {{ $user->isActive() ? 'text-[var(--danger)] hover:bg-[var(--danger-tint)]' : 'text-[var(--success)] hover:bg-[var(--success-tint)]' }}">
                                            {{ $user->isActive() ? 'Deactivate' : 'Reactivate' }}
                                        </button>
                                    @else
                                        <span class="px-2 py-1 text-[10px] font-mono text-[var(--text-muted)] bg-[var(--bg-subtle)] rounded-[4px]">Root Protected</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-10 text-center text-xs text-[var(--text-muted)]">
                                No user accounts match the current filter or search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Account Inspection Modal (Solid Physical Geometry, NO BACKDROP BLUR) -->
    @if ($inspectedUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/55"
             role="dialog"
             aria-modal="true"
             aria-labelledby="admin-inspect-user-title"
             x-data
             @keydown.escape.window="$wire.closeInspectionModal()">
            <div class="modal-dialog-surface w-full max-w-lg rounded-[22px] border hairline-border bg-[var(--bg-surface-elevated)] p-6 space-y-5 shadow-modal"
                 @click.away="$wire.closeInspectionModal()">
                <div class="flex items-center justify-between pb-3 border-b hairline-border">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-[8px] bg-[var(--bg-subtle)] border hairline-border flex items-center justify-center font-mono font-bold text-xs text-[var(--accent-primary)]" aria-hidden="true">
                            #{{ $inspectedUser->id }}
                        </div>
                        <div>
                            <h3 id="admin-inspect-user-title" class="font-heading text-lg font-bold text-[var(--text-primary)]">
                                {{ $inspectedUser->name }}
                            </h3>
                            <div class="text-xs font-mono text-[var(--text-muted)]">{{ $inspectedUser->email }}</div>
                        </div>
                    </div>
                    <button wire:click="closeInspectionModal" aria-label="Close inspection modal" class="btn-icon !w-8 !h-8">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Profile & Status Grid -->
                    <div class="grid grid-cols-2 gap-3 p-4 rounded-[10px] bg-[var(--bg-subtle)] border hairline-border">
                        <div>
                            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Account Role</div>
                            <div class="font-medium text-[var(--text-primary)] mt-0.5">{{ ucfirst($inspectedUser->role) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Account Status</div>
                            <div class="mt-0.5">
                                @if($inspectedUser->isActive())
                                    <span class="text-[var(--success)] font-mono font-semibold">Active</span>
                                @else
                                    <span class="text-[var(--danger)] font-mono font-semibold">Deactivated</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Cohort Standing</div>
                            <div class="font-mono text-[var(--text-primary)] mt-0.5">{{ $inspectedUser->academic_year ?? 'Unspecified' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Account Created</div>
                            <div class="font-mono text-[var(--text-muted)] mt-0.5">{{ $inspectedUser->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>

                    <!-- Financial Baselines -->
                    <div class="p-4 rounded-[10px] border hairline-border space-y-3 bg-[var(--bg-surface)]">
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Student Financial Baselines</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="metric-tile p-3">
                                <div class="text-[10px] text-[var(--text-muted)]">Monthly Allowance / Inflow</div>
                                <div class="font-mono text-lg font-bold text-[var(--text-primary)] tabular-nums mt-0.5">
                                    ${{ number_format($inspectedUser->monthly_allowance, 2) }}
                                </div>
                            </div>
                            <div class="metric-tile p-3">
                                <div class="text-[10px] text-[var(--text-muted)]">Target Monthly Savings Goal</div>
                                <div class="font-mono text-lg font-bold text-[var(--success)] tabular-nums mt-0.5">
                                    ${{ number_format($inspectedUser->savings_goal, 2) }}
                                </div>
                            </div>
                        </div>
                        @if($inspectedUser->isStudent())
                            <div class="pt-2 border-t hairline-border">
                                <button type="button" 
                                        wire:click="resetStudentFinancialBaselines({{ $inspectedUser->id }})"
                                        wire:confirm="Reset financial baselines (monthly allowance and savings target) to zero for this student?"
                                        class="px-3 py-1.5 text-xs font-mono rounded-[6px] border hairline-border text-[var(--gold-hover)] hover:bg-[var(--gold-tint)] transition-colors">
                                    Reset Financial Baselines to Zero
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Ledger & Operational Activity -->
                    <div class="p-4 rounded-[10px] border hairline-border space-y-3 bg-[var(--bg-surface)]">
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Ledger & Engagement Telemetry</div>
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div class="metric-tile p-2.5">
                                <div class="font-mono text-lg font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->transactions_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase mt-0.5">Transactions</div>
                            </div>
                            <div class="metric-tile p-2.5">
                                <div class="font-mono text-lg font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->budgets_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase mt-0.5">Budgets</div>
                            </div>
                            <div class="metric-tile p-2.5">
                                <div class="font-mono text-lg font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->saving_tips_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase mt-0.5">Tips</div>
                            </div>
                            <div class="metric-tile p-2.5">
                                <div class="font-mono text-lg font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->category_learnings_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase mt-0.5">Learned Mappings</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t hairline-border">
                    <div>
                        @if(! $inspectedUser->isAdmin())
                            <button type="button" 
                                    wire:click="toggleStatus({{ $inspectedUser->id }})"
                                    wire:confirm="{{ $inspectedUser->isActive() ? 'Deactivate this student account and terminate their active sessions?' : 'Reactivate this student account?' }}"
                                    class="px-3.5 py-2 rounded-[10px] text-xs font-mono font-medium border hairline-border transition-colors {{ $inspectedUser->isActive() ? 'text-[var(--danger)] hover:bg-[var(--danger-tint)]' : 'text-[var(--success)] hover:bg-[var(--success-tint)]' }}">
                                {{ $inspectedUser->isActive() ? 'Deactivate Account' : 'Reactivate Account' }}
                            </button>
                        @endif
                    </div>
                    <button type="button" wire:click="closeInspectionModal" class="btn-secondary !text-xs !min-h-[38px] !py-2 !px-4">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
