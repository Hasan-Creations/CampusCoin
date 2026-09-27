<div class="space-y-6">
    <!-- Feedback Alerts -->
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

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b hairline-border">
        <div>
            <h1 class="font-display text-2xl sm:text-3xl font-medium tracking-tight text-[var(--ink)] headline-rule">System Operations & Metric Telemetry</h1>
            <p class="text-xs sm:text-sm text-[var(--muted)] mt-1.5">Manage student accounts, shared categories, and usage metrics.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-button variant="secondary" href="{{ route('admin.categories') }}">
                <x-icon name="tag" class="w-3.5 h-3.5" />
                <span>Manage Categories</span>
            </x-button>
            <x-button variant="accent" href="{{ route('admin.users') }}">
                <x-icon name="users" class="w-3.5 h-3.5" />
                <span>Student Governance</span>
            </x-button>
        </div>
    </div>

    <!-- 4 High-Level Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Students Metric -->
        <div class="p-5 space-y-3 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">
                <span>Total Students</span>
            </div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">
                {{ number_format($metrics['students']['total']) }}
            </div>
            <div class="flex items-center justify-between text-[11px] font-mono text-[var(--muted)] pt-2 border-t hairline-border">
                <span>{{ $metrics['students']['active'] }} active <span class="opacity-80">({{ $metrics['students']['active_percentage'] }}%)</span></span>
                @if($metrics['students']['disabled'] > 0)
                    <span class="text-[var(--expense)] font-medium">{{ $metrics['students']['disabled'] }} disabled</span>
                @else
                    <span class="text-[var(--accent)]">0 disabled</span>
                @endif
            </div>
        </div>

        <!-- Platform Ledger Volume -->
        <div class="p-5 space-y-3 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">
                <span>Tracked Ledger Volume</span>
            </div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">
                ${{ number_format((float) $metrics['transactions']['total_volume'], 2) }}
            </div>
            <div class="flex items-center justify-between text-[11px] font-mono text-[var(--muted)] pt-2 border-t hairline-border">
                <span class="text-[var(--expense)] font-medium">-${{ number_format((float) $metrics['transactions']['expense_volume'], 2) }} Out</span>
                <span class="text-[var(--accent)] font-medium">+${{ number_format((float) $metrics['transactions']['income_volume'], 2) }} In</span>
            </div>
        </div>

        <!-- Ledger Transactions Count -->
        <div class="p-5 space-y-3 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">
                <span>Logged Transactions</span>
            </div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">
                {{ number_format($metrics['transactions']['total_count']) }}
            </div>
            <div class="flex items-center justify-between text-[11px] font-mono text-[var(--muted)] pt-2 border-t hairline-border">
                <span>Avg: ${{ $metrics['transactions']['avg_amount'] }}</span>
                <span class="text-[var(--accent)] font-medium">{{ $metrics['transactions']['recent_30d_count'] }} in 30d</span>
            </div>
        </div>

        <!-- Category & Budget Coverage -->
        <div class="p-5 space-y-3 bg-[var(--panel)]">
            <div class="text-xs font-caps text-[var(--muted)]">
                <span>Category Governance</span>
            </div>
            <div class="font-mono text-2xl sm:text-3xl font-medium text-[var(--ink)] tabular-nums tracking-tight">
                {{ $metrics['categories']['global'] }} <span class="text-sm font-normal text-[var(--muted)]">Global / {{ $metrics['categories']['personal'] }} Pers.</span>
            </div>
            <div class="flex items-center justify-between text-[11px] font-mono text-[var(--muted)] pt-2 border-t hairline-border">
                <span>{{ $metrics['categories']['active'] }} active</span>
                <span class="text-[var(--ink)]">{{ $metrics['budgets']['total_budgets'] }} budgets set</span>
            </div>
        </div>
    </div>

    <!-- Secondary Row: Most-Used Categories & Cohort Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Most-Used Categories Table (SRS Explicit) -->
        <div class="lg:col-span-7 card-campus p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b hairline-border">
                <div>
                    <h2 class="font-display text-base font-medium text-[var(--ink)]">Most-Used Categories</h2>
                    <p class="text-xs text-[var(--muted)] mt-0.5">System-wide transaction frequency and volume distribution</p>
                </div>
                <a href="{{ route('admin.categories') }}" class="text-xs font-sans text-[var(--accent)] hover:underline inline-flex items-center gap-1 font-medium">
                    Manage All &rarr;
                </a>
            </div>

            @if(count($metrics['categories']['most_used']) > 0)
                <div class="overflow-x-auto border hairline-border">
                    <table class="ledger-table w-full text-xs text-left">
                        <thead>
                            <tr>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col" class="text-right">Transactions</th>
                                <th scope="col" class="text-right">Volume</th>
                                <th scope="col" class="text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['categories']['most_used'] as $cat)
                                <tr class="table-row-tactile">
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $cat['color'] }}"></span>
                                            <span class="font-medium text-[var(--ink)]">{{ $cat['name'] }}</span>
                                            @if($cat['is_default'])
                                                <span class="px-1.5 py-0.5 text-[10px] font-caps border hairline-border text-[var(--accent)]">Default</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <x-badge :variant="$cat['type'] === 'income' ? 'income' : 'expense'">
                                            {{ ucfirst($cat['type']) }}
                                        </x-badge>
                                    </td>
                                    <td class="text-right font-mono tabular-nums text-[var(--ink)] font-medium">
                                        {{ number_format($cat['count']) }}
                                    </td>
                                    <td class="text-right font-mono tabular-nums text-[var(--ink)] font-medium">
                                        ${{ number_format((float) $cat['volume'], 2) }}
                                    </td>
                                    <td class="text-right font-mono tabular-nums text-[var(--muted)]">
                                        {{ $cat['percentage'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-xs text-[var(--muted)] border border-dashed hairline-border">
                    <x-icon name="tag" class="w-6 h-6 mx-auto mb-2 text-[var(--muted)]" />
                    No transactions recorded across the platform yet.
                </div>
            @endif
        </div>

        <!-- Student Cohorts & Allowance Baseline -->
        <div class="lg:col-span-5 card-campus p-6 space-y-4">
            <div class="pb-3 border-b hairline-border">
                <h2 class="font-display text-base font-medium text-[var(--ink)]">Student Demographics & Commitments</h2>
                <p class="text-xs text-[var(--muted)] mt-0.5">Cohort representation and baseline stipends</p>
            </div>

            <div class="space-y-3">
                <div class="border hairline-border bg-[var(--paper)] flex items-center justify-between p-3.5">
                    <div>
                        <div class="text-[11px] font-caps text-[var(--muted)]">Total Monthly Baseline Stipend</div>
                        <div class="text-[11px] text-[var(--muted)]">Aggregated student allowance</div>
                    </div>
                    <span class="font-mono font-medium text-base text-[var(--ink)] tabular-nums">
                        ${{ number_format((float) $metrics['students']['total_monthly_allowance'], 2) }}
                    </span>
                </div>
                <div class="border hairline-border bg-[var(--paper)] flex items-center justify-between p-3.5">
                    <div>
                        <div class="text-[11px] font-caps text-[var(--muted)]">Total Monthly Savings Targets</div>
                        <div class="text-[11px] text-[var(--muted)]">Aggregated goals commit</div>
                    </div>
                    <span class="font-mono font-medium text-base text-[var(--accent)] tabular-nums">
                        ${{ number_format((float) $metrics['students']['total_savings_goal'], 2) }}
                    </span>
                </div>
            </div>

            <div class="pt-2">
                <h3 class="text-xs font-caps text-[var(--muted)] mb-3">Cohort Enrollment Distribution</h3>
                <div class="space-y-2.5">
                    @forelse($metrics['students']['cohort_distribution'] as $cohort => $count)
                        <div class="p-2.5 bg-[var(--paper)] border hairline-border flex items-center justify-between text-xs">
                            <span class="font-medium text-[var(--ink)]">{{ $cohort }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-mono tabular-nums text-[var(--muted)]">{{ $count }} student{{ $count > 1 ? 's' : '' }}</span>
                                <div class="w-20 h-2 bg-[var(--panel)] border hairline-border overflow-hidden">
                                    <div class="h-full bg-[var(--accent)] transition-all" style="width: {{ $metrics['students']['total'] > 0 ? ($count / $metrics['students']['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-[var(--muted)] py-4 text-center">No cohort data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent Registered Student Accounts with Quick Action -->
    <div class="card-campus p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b hairline-border">
            <div>
                <h2 class="font-display text-base font-medium text-[var(--ink)]">Recent Registered Campus Accounts</h2>
                <p class="text-xs text-[var(--muted)] mt-0.5">Latest student signups with active status and quick administrative actions</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-xs font-sans text-[var(--accent)] hover:underline inline-flex items-center gap-1 font-medium">
                View All Accounts &rarr;
            </a>
        </div>

        <div class="overflow-x-auto border hairline-border">
            <table class="ledger-table w-full text-xs text-left">
                <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Cohort</th>
                        <th scope="col" class="text-right">Baseline Stipend</th>
                        <th scope="col" class="text-center">Transactions</th>
                        <th scope="col" class="text-center">Budgets</th>
                        <th scope="col">Status</th>
                        <th scope="col">Registered</th>
                        <th scope="col" class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents as $student)
                        <tr class="table-row-tactile">
                            <td>
                                <div class="font-medium text-[var(--ink)]">{{ $student->name }}</div>
                                <div class="font-mono text-[11px] text-[var(--muted)]">{{ $student->email }}</div>
                            </td>
                            <td class="font-mono text-[var(--muted)]">
                                {{ $student->academic_year ?? 'Unspecified' }}
                            </td>
                            <td class="font-mono tabular-nums text-right text-[var(--ink)] font-medium">
                                ${{ number_format($student->monthly_allowance, 2) }}
                            </td>
                            <td class="font-mono tabular-nums text-center text-[var(--muted)]">
                                {{ $student->transactions_count }}
                            </td>
                            <td class="font-mono tabular-nums text-center text-[var(--muted)]">
                                {{ $student->budgets_count }}
                            </td>
                            <td>
                                <x-badge :variant="$student->isActive() ? 'income' : 'expense'">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $student->isActive() ? 'bg-[var(--accent)]' : 'bg-[var(--expense)]' }}"></span>
                                    <span>{{ $student->isActive() ? 'Active' : 'Disabled' }}</span>
                                </x-badge>
                            </td>
                            <td class="font-mono text-[var(--muted)]">
                                {{ $student->created_at->format('Y-m-d') }}
                            </td>
                            <td class="text-right">
                                <button wire:click="toggleStudentStatus({{ $student->id }})" 
                                        aria-label="{{ $student->isActive() ? 'Deactivate student account for ' . $student->name : 'Reactivate student account for ' . $student->name }}"
                                        wire:confirm="{{ $student->isActive() ? 'Are you sure you want to deactivate ' . $student->name . '? Their active sessions will be terminated immediately.' : 'Reactivate account for ' . $student->name . '?' }}"
                                        class="px-2.5 py-1 text-xs font-mono border hairline-border transition-colors {{ $student->isActive() ? 'text-[var(--expense)] border-[var(--hairline)] hover:border-[var(--expense)]' : 'text-[var(--accent)] border-[var(--hairline)] hover:border-[var(--accent)]' }}">
                                    {{ $student->isActive() ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-xs text-[var(--muted)]">
                                No registered students found in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
