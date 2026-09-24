<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if ($feedbackMessage)
        <div role="status" aria-live="polite" class="p-4 rounded-[6px] border hairline-border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span>{{ $feedbackMessage }}</span>
            </div>
            <button wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div role="alert" aria-live="assertive" class="p-4 rounded-[6px] border hairline-border bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="shield-alert" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" />
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" aria-label="Dismiss error message" class="text-rose-600 hover:text-rose-800 dark:hover:text-rose-200">
                <x-icon name="x" class="w-3.5 h-3.5" />
            </button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-[var(--text-primary)]">System Operations & Metric Telemetry</h1>
            <p class="text-xs text-[var(--text-muted)] mt-1">Platform-level student governance, global category administration, and operational metrics</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] border hairline-border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-mono">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                SYSTEM OPERATIONAL
            </span>
            <a href="{{ route('admin.categories') }}" class="px-3 py-1.5 rounded-[6px] text-xs font-medium border hairline-border bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)] text-[var(--text-primary)] transition-colors inline-flex items-center gap-1.5">
                <x-icon name="tag" class="w-3.5 h-3.5 text-[var(--gold)]" />
                Manage Categories
            </a>
            <a href="{{ route('admin.users') }}" class="px-3 py-1.5 rounded-[6px] text-xs font-medium bg-[var(--accent-primary)] hover:bg-[var(--accent-hover)] text-white transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <x-icon name="users" class="w-3.5 h-3.5 text-white" />
                Student Governance
            </a>
        </div>
    </div>

    <!-- 4 High-Level Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Students Metric -->
        <div class="card-campus border hairline-border p-5 space-y-2">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Total Students</span>
                <x-icon name="users" class="w-4 h-4 text-[var(--accent-primary)]" />
            </div>
            <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                {{ number_format($metrics['students']['total']) }}
            </div>
            <div class="flex items-center justify-between text-[11px] text-[var(--text-muted)] pt-1 border-t hairline-border">
                <span>{{ $metrics['students']['active'] }} active ({{ $metrics['students']['active_percentage'] }}%)</span>
                @if($metrics['students']['disabled'] > 0)
                    <span class="text-rose-600 dark:text-rose-400 font-mono">{{ $metrics['students']['disabled'] }} disabled</span>
                @else
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono">0 disabled</span>
                @endif
            </div>
        </div>

        <!-- Platform Ledger Volume -->
        <div class="card-campus border hairline-border p-5 space-y-2">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Tracked Ledger Volume</span>
                <x-icon name="dollar-sign" class="w-4 h-4 text-[var(--gold)]" />
            </div>
            <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                ${{ number_format((float) $metrics['transactions']['total_volume'], 2) }}
            </div>
            <div class="flex items-center justify-between text-[11px] text-[var(--text-muted)] pt-1 border-t hairline-border">
                <span class="text-rose-600 dark:text-rose-400 font-mono">-${{ number_format((float) $metrics['transactions']['expense_volume'], 2) }} Out</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-mono">+${{ number_format((float) $metrics['transactions']['income_volume'], 2) }} In</span>
            </div>
        </div>

        <!-- Ledger Transactions Count -->
        <div class="card-campus border hairline-border p-5 space-y-2">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Logged Transactions</span>
                <x-icon name="activity" class="w-4 h-4 text-sky-500" />
            </div>
            <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                {{ number_format($metrics['transactions']['total_count']) }}
            </div>
            <div class="flex items-center justify-between text-[11px] text-[var(--text-muted)] pt-1 border-t hairline-border">
                <span>Avg: ${{ $metrics['transactions']['avg_amount'] }}</span>
                <span class="font-mono text-sky-600 dark:text-sky-400">{{ $metrics['transactions']['recent_30d_count'] }} in 30d</span>
            </div>
        </div>

        <!-- Category & Budget Coverage -->
        <div class="card-campus border hairline-border p-5 space-y-2">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Category Governance</span>
                <x-icon name="tag" class="w-4 h-4 text-emerald-500" />
            </div>
            <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                {{ $metrics['categories']['global'] }} <span class="text-xs font-normal text-[var(--text-muted)]">Global / {{ $metrics['categories']['personal'] }} Personal</span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-[var(--text-muted)] pt-1 border-t hairline-border">
                <span>{{ $metrics['categories']['active'] }} active</span>
                <span>{{ $metrics['budgets']['total_budgets'] }} budgets set</span>
            </div>
        </div>
    </div>

    <!-- Secondary Row: Most-Used Categories (SRS Required) & Cohort Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Most-Used Categories Table (SRS Explicit) -->
        <div class="lg:col-span-7 card-campus border hairline-border p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b hairline-border">
                <div>
                    <h2 class="font-heading text-sm font-bold text-[var(--text-primary)]">Most-Used Categories</h2>
                    <p class="text-[11px] text-[var(--text-muted)]">System-wide transaction frequency and volume distribution</p>
                </div>
                <a href="{{ route('admin.categories') }}" class="text-xs font-mono text-[var(--accent-primary)] hover:underline inline-flex items-center gap-1">
                    Manage All
                    <x-icon name="arrow-right" class="w-3 h-3" />
                </a>
            </div>

            @if(count($metrics['categories']['most_used']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)]">
                            <tr>
                                <th scope="col" class="p-2.5">Category</th>
                                <th scope="col" class="p-2.5">Type</th>
                                <th scope="col" class="p-2.5 text-right">Transactions</th>
                                <th scope="col" class="p-2.5 text-right">Volume</th>
                                <th scope="col" class="p-2.5 text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y hairline-border">
                            @foreach($metrics['categories']['most_used'] as $cat)
                                <tr class="hover:bg-[var(--bg-subtle)]/50 transition-colors">
                                    <td class="p-2.5">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $cat['color'] }}"></span>
                                            <span class="font-medium text-[var(--text-primary)]">{{ $cat['name'] }}</span>
                                            @if($cat['is_default'])
                                                <span class="px-1.5 py-0.5 rounded-[3px] text-[10px] font-mono bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300">Default</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2.5">
                                        <span class="badge-campus {{ $cat['type'] === 'income' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-400' }}">
                                            {{ ucfirst($cat['type']) }}
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-right font-mono tabular-nums text-[var(--text-primary)]">
                                        {{ number_format($cat['count']) }}
                                    </td>
                                    <td class="p-2.5 text-right font-mono tabular-nums text-[var(--text-primary)]">
                                        ${{ number_format((float) $cat['volume'], 2) }}
                                    </td>
                                    <td class="p-2.5 text-right font-mono tabular-nums text-[var(--text-muted)]">
                                        {{ $cat['percentage'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-xs text-[var(--text-muted)]">
                    No transactions recorded across the platform yet.
                </div>
            @endif
        </div>

        <!-- Student Cohorts & Allowance Baseline -->
        <div class="lg:col-span-5 card-campus border hairline-border p-6 space-y-4">
            <div class="pb-3 border-b hairline-border">
                <h2 class="font-heading text-sm font-bold text-[var(--text-primary)]">Student Demographics & Commitments</h2>
                <p class="text-[11px] text-[var(--text-muted)]">Cohort representation and baseline stipends</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-[6px] bg-[var(--bg-subtle)] border hairline-border">
                    <span class="text-xs text-[var(--text-muted)]">Total Monthly Baseline Stipend</span>
                    <span class="font-mono font-bold text-sm text-[var(--text-primary)] tabular-nums">
                        ${{ number_format((float) $metrics['students']['total_monthly_allowance'], 2) }}
                    </span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-[6px] bg-[var(--bg-subtle)] border hairline-border">
                    <span class="text-xs text-[var(--text-muted)]">Total Monthly Savings Targets</span>
                    <span class="font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400 tabular-nums">
                        ${{ number_format((float) $metrics['students']['total_savings_goal'], 2) }}
                    </span>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-mono uppercase tracking-wider text-[var(--text-muted)] mb-2">Cohort Enrollment</h3>
                <div class="space-y-2">
                    @forelse($metrics['students']['cohort_distribution'] as $cohort => $count)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-[var(--text-primary)]">{{ $cohort }}</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono tabular-nums text-[var(--text-muted)]">{{ $count }} student{{ $count > 1 ? 's' : '' }}</span>
                                <div class="w-16 h-1.5 rounded-full bg-[var(--bg-subtle)] overflow-hidden">
                                    <div class="h-full bg-[var(--accent-primary)]" style="width: {{ $metrics['students']['total'] > 0 ? ($count / $metrics['students']['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-[var(--text-muted)]">No cohort data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent Registered Student Accounts with Quick Action -->
    <div class="card-campus border hairline-border p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b hairline-border">
            <div>
                <h2 class="font-heading text-sm font-bold text-[var(--text-primary)]">Recent Registered Campus Accounts</h2>
                <p class="text-[11px] text-[var(--text-muted)]">Latest student signups with active status and quick administrative actions</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-xs font-mono text-[var(--accent-primary)] hover:underline inline-flex items-center gap-1">
                View All Accounts
                <x-icon name="arrow-right" class="w-3 h-3" />
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)]">
                    <tr>
                        <th scope="col" class="p-3">Student</th>
                        <th scope="col" class="p-3">Cohort</th>
                        <th scope="col" class="p-3 text-right">Baseline Stipend</th>
                        <th scope="col" class="p-3 text-center">Transactions</th>
                        <th scope="col" class="p-3 text-center">Budgets</th>
                        <th scope="col" class="p-3">Status</th>
                        <th scope="col" class="p-3">Registered</th>
                        <th scope="col" class="p-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y hairline-border">
                    @forelse($recentStudents as $student)
                        <tr class="hover:bg-[var(--bg-subtle)]/50 transition-colors">
                            <td class="p-3">
                                <div class="font-medium text-[var(--text-primary)]">{{ $student->name }}</div>
                                <div class="font-mono text-[11px] text-[var(--text-muted)]">{{ $student->email }}</div>
                            </td>
                            <td class="p-3 font-mono text-[var(--text-muted)]">
                                {{ $student->academic_year ?? 'Unspecified' }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-right text-[var(--text-primary)]">
                                ${{ number_format($student->monthly_allowance, 2) }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-center text-[var(--text-muted)]">
                                {{ $student->transactions_count }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-center text-[var(--text-muted)]">
                                {{ $student->budgets_count }}
                            </td>
                            <td class="p-3">
                                @if($student->isActive())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[10px] font-mono bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400">
                                        <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 font-mono text-[var(--text-muted)]">
                                {{ $student->created_at->format('Y-m-d') }}
                            </td>
                            <td class="p-3 text-right">
                                <button wire:click="toggleStudentStatus({{ $student->id }})" 
                                        aria-label="{{ $student->isActive() ? 'Deactivate student account for ' . $student->name : 'Reactivate student account for ' . $student->name }}"
                                        wire:confirm="{{ $student->isActive() ? 'Are you sure you want to deactivate ' . $student->name . '? Their active sessions will be terminated immediately.' : 'Reactivate account for ' . $student->name . '?' }}"
                                        class="px-2.5 py-1 rounded-[4px] text-xs font-mono border hairline-border transition-colors {{ $student->isActive() ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                                    {{ $student->isActive() ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-xs text-[var(--text-muted)]">
                                No registered students found in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
