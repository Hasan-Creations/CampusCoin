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
                <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-mono uppercase tracking-wider bg-[var(--gold-tint)] text-[var(--gold-hover)] border hairline-border font-bold">
                    Ops Console
                </span>
                <span class="text-xs text-[var(--text-muted)] font-mono">Platform Telemetry</span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-bold tracking-tight text-[var(--text-primary)] mt-1">System Operations & Metric Telemetry</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">Platform-level student governance, global category administration, and operational metrics</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] text-[var(--success)] text-xs font-mono font-semibold">
                <span class="w-2 h-2 rounded-full bg-[var(--success)] animate-pulse"></span>
                SYSTEM OPERATIONAL
            </span>
            <a href="{{ route('admin.categories') }}" class="btn-secondary !text-xs !min-h-[38px] !py-2 !px-3.5 inline-flex items-center gap-1.5">
                <x-icon name="tag" class="w-3.5 h-3.5 text-[var(--gold)]" />
                Manage Categories
            </a>
            <a href="{{ route('admin.users') }}" class="btn-primary !text-xs !min-h-[38px] !py-2 !px-3.5 inline-flex items-center gap-1.5">
                <x-icon name="users" class="w-3.5 h-3.5 text-white" />
                Student Governance
            </a>
        </div>
    </div>

    <!-- 4 High-Level Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Students Metric -->
        <div class="card-campus p-5 space-y-3">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Total Students</span>
                <div class="w-7 h-7 rounded-[6px] bg-[var(--bg-subtle)] flex items-center justify-center text-[var(--accent-primary)]">
                    <x-icon name="users" class="w-4 h-4" />
                </div>
            </div>
            <div class="font-mono text-3xl font-bold text-[var(--text-primary)] tabular-nums tracking-tight">
                {{ number_format($metrics['students']['total']) }}
            </div>
            <div class="flex items-center justify-between text-xs text-[var(--text-muted)] pt-2 border-t hairline-border">
                <span>{{ $metrics['students']['active'] }} active <span class="text-[11px] opacity-80">({{ $metrics['students']['active_percentage'] }}%)</span></span>
                @if($metrics['students']['disabled'] > 0)
                    <span class="text-[var(--danger)] font-mono font-semibold">{{ $metrics['students']['disabled'] }} disabled</span>
                @else
                    <span class="text-[var(--success)] font-mono">0 disabled</span>
                @endif
            </div>
        </div>

        <!-- Platform Ledger Volume -->
        <div class="card-campus p-5 space-y-3">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Tracked Ledger Volume</span>
                <div class="w-7 h-7 rounded-[6px] bg-[var(--gold-tint)] flex items-center justify-center text-[var(--gold)]">
                    <x-icon name="dollar-sign" class="w-4 h-4" />
                </div>
            </div>
            <div class="font-mono text-3xl font-bold text-[var(--text-primary)] tabular-nums tracking-tight">
                ${{ number_format((float) $metrics['transactions']['total_volume'], 2) }}
            </div>
            <div class="flex items-center justify-between text-xs text-[var(--text-muted)] pt-2 border-t hairline-border">
                <span class="text-[var(--danger)] font-mono font-medium">-${{ number_format((float) $metrics['transactions']['expense_volume'], 2) }} Out</span>
                <span class="text-[var(--success)] font-mono font-medium">+${{ number_format((float) $metrics['transactions']['income_volume'], 2) }} In</span>
            </div>
        </div>

        <!-- Ledger Transactions Count -->
        <div class="card-campus p-5 space-y-3">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Logged Transactions</span>
                <div class="w-7 h-7 rounded-[6px] bg-[var(--bg-subtle)] flex items-center justify-center text-[var(--accent-primary)]">
                    <x-icon name="activity" class="w-4 h-4" />
                </div>
            </div>
            <div class="font-mono text-3xl font-bold text-[var(--text-primary)] tabular-nums tracking-tight">
                {{ number_format($metrics['transactions']['total_count']) }}
            </div>
            <div class="flex items-center justify-between text-xs text-[var(--text-muted)] pt-2 border-t hairline-border">
                <span>Avg: ${{ $metrics['transactions']['avg_amount'] }}</span>
                <span class="font-mono text-[var(--accent-primary)] font-medium">{{ $metrics['transactions']['recent_30d_count'] }} in 30d</span>
            </div>
        </div>

        <!-- Category & Budget Coverage -->
        <div class="card-campus p-5 space-y-3">
            <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                <span>Category Governance</span>
                <div class="w-7 h-7 rounded-[6px] bg-[var(--bg-subtle)] flex items-center justify-center text-[var(--gold)]">
                    <x-icon name="tag" class="w-4 h-4" />
                </div>
            </div>
            <div class="font-mono text-3xl font-bold text-[var(--text-primary)] tabular-nums tracking-tight">
                {{ $metrics['categories']['global'] }} <span class="text-sm font-normal text-[var(--text-muted)]">Global / {{ $metrics['categories']['personal'] }} Pers.</span>
            </div>
            <div class="flex items-center justify-between text-xs text-[var(--text-muted)] pt-2 border-t hairline-border">
                <span>{{ $metrics['categories']['active'] }} active</span>
                <span class="font-mono text-[var(--text-primary)]">{{ $metrics['budgets']['total_budgets'] }} budgets set</span>
            </div>
        </div>
    </div>

    <!-- Secondary Row: Most-Used Categories & Cohort Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Most-Used Categories Table (SRS Explicit) -->
        <div class="lg:col-span-7 card-campus p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b hairline-border">
                <div>
                    <h2 class="font-heading text-base font-bold text-[var(--text-primary)]">Most-Used Categories</h2>
                    <p class="text-xs text-[var(--text-muted)]">System-wide transaction frequency and volume distribution</p>
                </div>
                <a href="{{ route('admin.categories') }}" class="text-xs font-mono text-[var(--accent-primary)] hover:underline inline-flex items-center gap-1 font-medium">
                    Manage All
                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            @if(count($metrics['categories']['most_used']) > 0)
                <div class="overflow-x-auto rounded-[10px] border hairline-border">
                    <table class="w-full text-xs text-left">
                        <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)] border-b hairline-border">
                            <tr>
                                <th scope="col" class="p-3">Category</th>
                                <th scope="col" class="p-3">Type</th>
                                <th scope="col" class="p-3 text-right">Transactions</th>
                                <th scope="col" class="p-3 text-right">Volume</th>
                                <th scope="col" class="p-3 text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y hairline-border bg-[var(--bg-surface)]">
                            @foreach($metrics['categories']['most_used'] as $cat)
                                <tr class="table-row-tactile">
                                    <td class="p-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-3 h-3 rounded-full shrink-0 shadow-xs" style="background-color: {{ $cat['color'] }}"></span>
                                            <span class="font-medium text-[var(--text-primary)]">{{ $cat['name'] }}</span>
                                            @if($cat['is_default'])
                                                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-mono bg-[var(--bg-subtle)] border hairline-border text-[var(--accent-primary)] font-medium">Default</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <span class="badge-campus {{ $cat['type'] === 'income' ? 'bg-[var(--success-tint)] text-[var(--success)]' : 'bg-[var(--danger-tint)] text-[var(--danger)]' }}">
                                            {{ ucfirst($cat['type']) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right font-mono tabular-nums text-[var(--text-primary)] font-medium">
                                        {{ number_format($cat['count']) }}
                                    </td>
                                    <td class="p-3 text-right font-mono tabular-nums text-[var(--text-primary)] font-bold">
                                        ${{ number_format((float) $cat['volume'], 2) }}
                                    </td>
                                    <td class="p-3 text-right font-mono tabular-nums text-[var(--text-muted)]">
                                        {{ $cat['percentage'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-xs text-[var(--text-muted)] metric-tile">
                    <x-icon name="tag" class="w-6 h-6 mx-auto mb-2 opacity-50" />
                    No transactions recorded across the platform yet.
                </div>
            @endif
        </div>

        <!-- Student Cohorts & Allowance Baseline -->
        <div class="lg:col-span-5 card-campus p-6 space-y-4">
            <div class="pb-3 border-b hairline-border">
                <h2 class="font-heading text-base font-bold text-[var(--text-primary)]">Student Demographics & Commitments</h2>
                <p class="text-xs text-[var(--text-muted)]">Cohort representation and baseline stipends</p>
            </div>

            <div class="space-y-3">
                <div class="metric-tile flex items-center justify-between p-3.5">
                    <div>
                        <div class="text-[11px] font-mono uppercase text-[var(--text-muted)]">Total Monthly Baseline Stipend</div>
                        <div class="text-[11px] text-[var(--text-muted)]">Aggregated student allowance</div>
                    </div>
                    <span class="font-mono font-bold text-base text-[var(--text-primary)] tabular-nums">
                        ${{ number_format((float) $metrics['students']['total_monthly_allowance'], 2) }}
                    </span>
                </div>
                <div class="metric-tile flex items-center justify-between p-3.5">
                    <div>
                        <div class="text-[11px] font-mono uppercase text-[var(--text-muted)]">Total Monthly Savings Targets</div>
                        <div class="text-[11px] text-[var(--text-muted)]">Aggregated goals commit</div>
                    </div>
                    <span class="font-mono font-bold text-base text-[var(--success)] tabular-nums">
                        ${{ number_format((float) $metrics['students']['total_savings_goal'], 2) }}
                    </span>
                </div>
            </div>

            <div class="pt-2">
                <h3 class="text-xs font-mono uppercase tracking-wider text-[var(--text-muted)] mb-3">Cohort Enrollment Distribution</h3>
                <div class="space-y-2.5">
                    @forelse($metrics['students']['cohort_distribution'] as $cohort => $count)
                        <div class="p-2.5 rounded-[10px] bg-[var(--bg-subtle)] border hairline-border flex items-center justify-between text-xs">
                            <span class="font-medium text-[var(--text-primary)]">{{ $cohort }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-mono tabular-nums text-[var(--text-muted)]">{{ $count }} student{{ $count > 1 ? 's' : '' }}</span>
                                <div class="w-20 h-2 rounded-full bg-[var(--bg-surface)] border hairline-border overflow-hidden">
                                    <div class="h-full bg-[var(--accent-primary)] transition-all" style="width: {{ $metrics['students']['total'] > 0 ? ($count / $metrics['students']['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-[var(--text-muted)] py-4 text-center">No cohort data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent Registered Student Accounts with Quick Action -->
    <div class="card-campus p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b hairline-border">
            <div>
                <h2 class="font-heading text-base font-bold text-[var(--text-primary)]">Recent Registered Campus Accounts</h2>
                <p class="text-xs text-[var(--text-muted)]">Latest student signups with active status and quick administrative actions</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-xs font-mono text-[var(--accent-primary)] hover:underline inline-flex items-center gap-1 font-medium">
                View All Accounts
                <x-icon name="arrow-right" class="w-3.5 h-3.5" />
            </a>
        </div>

        <div class="overflow-x-auto rounded-[10px] border hairline-border">
            <table class="w-full text-xs text-left">
                <thead class="font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)] border-b hairline-border">
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
                <tbody class="divide-y hairline-border bg-[var(--bg-surface)]">
                    @forelse($recentStudents as $student)
                        <tr class="table-row-tactile">
                            <td class="p-3">
                                <div class="font-medium text-[var(--text-primary)]">{{ $student->name }}</div>
                                <div class="font-mono text-[11px] text-[var(--text-muted)]">{{ $student->email }}</div>
                            </td>
                            <td class="p-3 font-mono text-[var(--text-muted)]">
                                {{ $student->academic_year ?? 'Unspecified' }}
                            </td>
                            <td class="p-3 font-mono tabular-nums text-right text-[var(--text-primary)] font-semibold">
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
                            <td class="p-3 font-mono text-[var(--text-muted)]">
                                {{ $student->created_at->format('Y-m-d') }}
                            </td>
                            <td class="p-3 text-right">
                                <button wire:click="toggleStudentStatus({{ $student->id }})" 
                                        aria-label="{{ $student->isActive() ? 'Deactivate student account for ' . $student->name : 'Reactivate student account for ' . $student->name }}"
                                        wire:confirm="{{ $student->isActive() ? 'Are you sure you want to deactivate ' . $student->name . '? Their active sessions will be terminated immediately.' : 'Reactivate account for ' . $student->name . '?' }}"
                                        class="px-2.5 py-1 rounded-[6px] text-xs font-mono border hairline-border transition-colors {{ $student->isActive() ? 'text-[var(--danger)] hover:bg-[var(--danger-tint)]' : 'text-[var(--success)] hover:bg-[var(--success-tint)]' }}">
                                    {{ $student->isActive() ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-xs text-[var(--text-muted)]">
                                No registered students found in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
