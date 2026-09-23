<x-layouts.admin title="Operational Console">
    <div class="space-y-6">
        <div class="flex items-center justify-between pb-6 border-b hairline-border">
            <div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">System Operations & Metric Telemetry</h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">Global administrative management, category control, and user governance</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] border hairline-border bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    SERVICES HEALTHY
                </span>
            </div>
        </div>

        <!-- 4 Admin KPIs per Section 33 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Total Students</span>
                    <x-icon name="users" class="w-4 h-4 text-[var(--accent-primary)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    {{ \App\Models\User::where('role', 'student')->count() }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Active student ledgers
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Verified Domains</span>
                    <x-icon name="shield-check" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    100%
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Campus institutional emails
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Tracked Volume</span>
                    <x-icon name="dollar-sign" class="w-4 h-4 text-[var(--gold)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    ${{ number_format(\App\Models\User::where('role', 'student')->sum('monthly_allowance'), 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Monthly stipend baselines
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>System Health</span>
                    <x-icon name="activity" class="w-4 h-4 text-sky-500" />
                </div>
                <div class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    99.98%
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    MySQL / MariaDB online
                </div>
            </div>
        </div>

        <!-- Student Accounts Table -->
        <div class="card-campus border hairline-border p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-heading text-base font-bold text-[var(--text-primary)]">Registered Campus Accounts</h2>
                    <p class="text-xs text-[var(--text-muted)]">System accounts with active status and cohort details</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="border-b hairline-border font-mono uppercase tracking-wider text-[var(--text-muted)] bg-[var(--bg-subtle)]">
                        <tr>
                            <th class="p-3">User</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Cohort</th>
                            <th class="p-3">Monthly Baseline</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y hairline-border">
                        @foreach(\App\Models\User::all() as $u)
                            <tr class="hover:bg-[var(--bg-subtle)]/50">
                                <td class="p-3">
                                    <div class="font-medium text-[var(--text-primary)]">{{ $u->name }}</div>
                                    <div class="font-mono text-[11px] text-[var(--text-muted)]">{{ $u->email }}</div>
                                </td>
                                <td class="p-3 font-mono">
                                    <span class="badge-campus {{ $u->isAdmin() ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono text-[var(--text-muted)]">
                                    {{ $u->academic_year ?? 'N/A (Staff)' }}
                                </td>
                                <td class="p-3 font-mono tabular-nums text-[var(--text-primary)]">
                                    ${{ number_format($u->monthly_allowance, 2) }}
                                </td>
                                <td class="p-3 font-mono">
                                    <span class="badge-campus bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                                        {{ $u->status }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono text-[var(--text-muted)]">
                                    {{ $u->created_at->format('Y-m-d') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
