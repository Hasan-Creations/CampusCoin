<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if ($feedbackMessage)
        <div class="p-4 rounded-[6px] border hairline-border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span>{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-[6px] border hairline-border bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="shield-alert" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" />
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" class="text-rose-600 hover:text-rose-800 dark:hover:text-rose-200">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-[var(--text-primary)]">Student Account Governance</h1>
            <p class="text-xs text-[var(--text-muted)] mt-1">Review student profiles, enforce account deactivations, inspect ledger activity, and reset baselines</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-[var(--text-primary)] text-xs font-mono">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                ACTIVE AUTH GUARDS
            </span>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-[6px] border hairline-border bg-[var(--bg-surface)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Total Registered Students</div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums mt-0.5">{{ number_format($totalStudents) }}</div>
            </div>
            <x-icon name="users" class="w-6 h-6 text-[var(--accent-primary)] opacity-70" />
        </div>

        <div class="p-4 rounded-[6px] border hairline-border bg-[var(--bg-surface)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-mono uppercase text-emerald-600 dark:text-emerald-400">Active Accounts</div>
                <div class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums mt-0.5">{{ number_format($activeStudents) }}</div>
            </div>
            <x-icon name="user-check" class="w-6 h-6 text-emerald-500 opacity-70" />
        </div>

        <div class="p-4 rounded-[6px] border hairline-border bg-[var(--bg-surface)] flex items-center justify-between">
            <div>
                <div class="text-[10px] font-mono uppercase text-rose-600 dark:text-rose-400">Disabled / Deactivated</div>
                <div class="font-mono text-2xl font-bold text-rose-600 dark:text-rose-400 tabular-nums mt-0.5">{{ number_format($disabledStudents) }}</div>
            </div>
            <x-icon name="user-x" class="w-6 h-6 text-rose-500 opacity-70" />
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card-campus border hairline-border p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Role Selector -->
            <div class="inline-flex p-1 rounded-[6px] bg-[var(--bg-subtle)] border hairline-border">
                <button wire:click="$set('filterRole', 'student')" 
                        class="px-3 py-1.5 rounded-[4px] text-xs font-medium transition-colors {{ $filterRole === 'student' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    Students
                </button>
                <button wire:click="$set('filterRole', 'admin')" 
                        class="px-3 py-1.5 rounded-[4px] text-xs font-medium transition-colors {{ $filterRole === 'admin' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    Administrators
                </button>
                <button wire:click="$set('filterRole', 'all')" 
                        class="px-3 py-1.5 rounded-[4px] text-xs font-medium transition-colors {{ $filterRole === 'all' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    All Users
                </button>
            </div>

            <!-- Controls: Status, Cohort, Sort, Search -->
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterStatus" class="px-2.5 py-1.5 rounded-[6px] text-xs border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)]">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Accounts Only</option>
                    <option value="disabled">Disabled Accounts Only</option>
                </select>

                <select wire:model.live="filterCohort" class="px-2.5 py-1.5 rounded-[6px] text-xs border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)]">
                    <option value="all">All Cohorts</option>
                    <option value="Freshman">Freshman</option>
                    <option value="Sophomore">Sophomore</option>
                    <option value="Junior">Junior</option>
                    <option value="Senior">Senior</option>
                    <option value="Graduate">Graduate</option>
                </select>

                <select wire:model.live="sortBy" class="px-2.5 py-1.5 rounded-[6px] text-xs border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)]">
                    <option value="latest">Sort: Newest First</option>
                    <option value="name_asc">Sort: Name (A-Z)</option>
                    <option value="transactions_desc">Sort: Highest Activity</option>
                </select>

                <div class="relative min-w-[200px]">
                    <x-icon name="search" class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                    <input type="text" 
                           wire:model.live.debounce.250ms="search" 
                           placeholder="Search name or email..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-[6px] border hairline-border bg-[var(--bg-surface)] text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:outline-hidden focus:ring-1 focus:ring-[var(--accent-primary)]" />
                </div>
            </div>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="card-campus border hairline-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)] border-b hairline-border">
                    <tr>
                        <th class="p-3">User & Email</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Cohort</th>
                        <th class="p-3 text-right">Monthly Stipend</th>
                        <th class="p-3 text-right">Savings Goal</th>
                        <th class="p-3 text-center">Ledger Activity</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Registered</th>
                        <th class="p-3 text-right">Administrative Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y hairline-border">
                    @forelse($users as $user)
                        <tr class="hover:bg-[var(--bg-subtle)]/50 transition-colors {{ ! $user->isActive() ? 'opacity-65' : '' }}">
                            <td class="p-3">
                                <div class="font-medium text-[var(--text-primary)]">{{ $user->name }}</div>
                                <div class="font-mono text-[11px] text-[var(--text-muted)]">{{ $user->email }}</div>
                            </td>
                            <td class="p-3">
                                <span class="badge-campus {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400' : 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-400' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="p-3 font-mono text-[var(--text-muted)]">
                                {{ $user->academic_year ?? 'Staff / Root' }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-right text-[var(--text-primary)]">
                                ${{ number_format($user->monthly_allowance, 2) }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-right text-emerald-600 dark:text-emerald-400">
                                ${{ number_format($user->savings_goal, 2) }}
                            </td>
                            <td class="p-3 font-mono text-center text-[var(--text-muted)]">
                                <span title="Transactions" class="tabular-nums font-semibold text-[var(--text-primary)]">{{ $user->transactions_count }}</span> tx / 
                                <span title="Budgets" class="tabular-nums">{{ $user->budgets_count }}</span> bgt
                            </td>
                            <td class="p-3">
                                @if($user->isActive())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 font-mono text-[var(--text-muted)]">
                                {{ $user->created_at->format('Y-m-d') }}
                            </td>
                            <td class="p-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Inspect Button -->
                                    <button wire:click="inspectUser({{ $user->id }})" 
                                            class="p-1.5 rounded-[4px] border hairline-border hover:bg-[var(--bg-subtle)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors"
                                            title="Inspect Account Details">
                                        <x-icon name="eye" class="w-3.5 h-3.5" />
                                    </button>

                                    <!-- Status Toggle Button -->
                                    @if (! $user->isAdmin())
                                        <button wire:click="toggleStatus({{ $user->id }})" 
                                                wire:confirm="{{ $user->isActive() ? 'Are you sure you want to deactivate ' . $user->name . '? Their active login sessions will be immediately terminated.' : 'Reactivate account for ' . $user->name . '?' }}"
                                                class="px-2.5 py-1 rounded-[4px] text-[11px] font-mono border hairline-border transition-colors {{ $user->isActive() ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                                            {{ $user->isActive() ? 'Deactivate' : 'Reactivate' }}
                                        </button>
                                    @else
                                        <span class="px-2 py-1 text-[10px] font-mono text-[var(--text-muted)]">Root Protected</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-xs text-[var(--text-muted)]">
                                No user accounts match the current filter or search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Account Inspection Modal -->
    @if ($inspectedUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-[8px] border hairline-border bg-[var(--bg-surface)] p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b hairline-border">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-[4px] bg-[var(--bg-subtle)] flex items-center justify-center font-mono font-bold text-xs">
                            #{{ $inspectedUser->id }}
                        </div>
                        <div>
                            <h3 class="font-heading text-base font-bold text-[var(--text-primary)]">
                                {{ $inspectedUser->name }}
                            </h3>
                            <div class="text-[11px] font-mono text-[var(--text-muted)]">{{ $inspectedUser->email }}</div>
                        </div>
                    </div>
                    <button wire:click="closeInspectionModal" class="p-1 rounded-[4px] hover:bg-[var(--bg-subtle)] text-[var(--text-muted)]">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Profile & Status Grid -->
                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-[6px] bg-[var(--bg-subtle)] border hairline-border">
                        <div>
                            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Account Role</div>
                            <div class="font-medium text-[var(--text-primary)] mt-0.5">{{ ucfirst($inspectedUser->role) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">Account Status</div>
                            <div class="mt-0.5">
                                @if($inspectedUser->isActive())
                                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-semibold">Active</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400 font-mono font-semibold">Deactivated</span>
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
                    <div class="p-3.5 rounded-[6px] border hairline-border space-y-2">
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Student Financial Baselines</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-[10px] text-[var(--text-muted)]">Monthly Allowance / Inflow</div>
                                <div class="font-mono text-base font-bold text-[var(--text-primary)] tabular-nums">
                                    ${{ number_format($inspectedUser->monthly_allowance, 2) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] text-[var(--text-muted)]">Target Monthly Savings Goal</div>
                                <div class="font-mono text-base font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    ${{ number_format($inspectedUser->savings_goal, 2) }}
                                </div>
                            </div>
                        </div>
                        @if($inspectedUser->isStudent())
                            <div class="pt-2 border-t hairline-border">
                                <button type="button" 
                                        wire:click="resetStudentFinancialBaselines({{ $inspectedUser->id }})"
                                        wire:confirm="Reset financial baselines (monthly allowance and savings target) to zero for this student?"
                                        class="px-2.5 py-1 text-[11px] font-mono rounded-[4px] border hairline-border text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors">
                                    Reset Financial Baselines to Zero
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Ledger & Operational Activity -->
                    <div class="p-3.5 rounded-[6px] border hairline-border space-y-2">
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Ledger & Engagement Telemetry</div>
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div class="p-2 rounded-[4px] bg-[var(--bg-subtle)]">
                                <div class="font-mono text-base font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->transactions_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase">Transactions</div>
                            </div>
                            <div class="p-2 rounded-[4px] bg-[var(--bg-subtle)]">
                                <div class="font-mono text-base font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->budgets_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase">Budgets</div>
                            </div>
                            <div class="p-2 rounded-[4px] bg-[var(--bg-subtle)]">
                                <div class="font-mono text-base font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->saving_tips_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase">Tips</div>
                            </div>
                            <div class="p-2 rounded-[4px] bg-[var(--bg-subtle)]">
                                <div class="font-mono text-base font-bold text-[var(--text-primary)] tabular-nums">{{ $inspectedUser->category_learnings_count }}</div>
                                <div class="text-[9px] font-mono text-[var(--text-muted)] uppercase">Learned Mappings</div>
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
                                    class="px-3 py-1.5 rounded-[6px] text-xs font-mono font-medium border hairline-border {{ $inspectedUser->isActive() ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                                {{ $inspectedUser->isActive() ? 'Deactivate Account' : 'Reactivate Account' }}
                            </button>
                        @endif
                    </div>
                    <button type="button" wire:click="closeInspectionModal" class="px-3.5 py-1.5 rounded-[6px] border hairline-border text-[var(--text-muted)] hover:text-[var(--text-primary)] text-xs font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
