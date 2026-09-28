<x-layouts.admin title="Operational Console">
    <div class="space-y-6">
        <div class="flex items-center justify-between pb-6 border-b hairline-border">
            <div>
                <h1 class="font-display text-2xl font-medium text-[var(--ink)] headline-rule">System Operations & Metric Telemetry</h1>
                <p class="text-xs text-[var(--muted)] mt-1">Global administrative management, category control, and user governance</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 border hairline-border bg-[var(--paper)] text-[var(--accent)] text-xs font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)] animate-pulse"></span>
                    SERVICES HEALTHY
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Total Students</span>
                    <x-icon name="users" class="w-4 h-4 text-[var(--accent)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums">
                    {{ \App\Models\User::where('role', 'student')->count() }}
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Active student ledgers
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Verified Domains</span>
                    <x-icon name="shield-check" class="w-4 h-4 text-[var(--accent)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums">
                    100%
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Campus institutional emails
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>Tracked Volume</span>
                    <x-icon name="dollar-sign" class="w-4 h-4 text-[var(--secondary)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums">
                    ${{ number_format(\App\Models\User::where('role', 'student')->sum('monthly_allowance'), 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    Monthly stipend baselines
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-caps text-[var(--muted)]">
                    <span>System Health</span>
                    <x-icon name="activity" class="w-4 h-4 text-[var(--accent)]" />
                </div>
                <div class="font-mono text-2xl font-medium text-[var(--accent)] tabular-nums">
                    99.98%
                </div>
                <div class="text-[11px] text-[var(--muted)]">
                    MySQL / MariaDB online
                </div>
            </div>
        </div>

        <div class="card-campus border hairline-border p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-base font-medium text-[var(--ink)]">Registered Campus Accounts</h2>
                    <p class="text-xs text-[var(--muted)]">System accounts with active status and cohort details</p>
                </div>
            </div>

            <div class="overflow-x-auto border hairline-border">
                <table class="ledger-table w-full text-xs text-left">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col">Cohort</th>
                            <th scope="col" class="text-right">Monthly Baseline</th>
                            <th scope="col">Status</th>
                            <th scope="col">Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\User::all() as $u)
                            <tr class="table-row-tactile">
                                <td>
                                    <div class="font-medium text-[var(--ink)]">{{ $u->name }}</div>
                                    <div class="font-mono text-[11px] text-[var(--muted)]">{{ $u->email }}</div>
                                </td>
                                <td>
                                    <x-badge :variant="$u->isAdmin() ? 'secondary' : 'default'">
                                        {{ $u->role }}
                                    </x-badge>
                                </td>
                                <td class="font-mono text-[var(--muted)]">
                                    {{ $u->academic_year ?? 'N/A (Staff)' }}
                                </td>
                                <td class="font-mono tabular-nums text-right text-[var(--ink)]">
                                    ${{ number_format($u->monthly_allowance, 2) }}
                                </td>
                                <td>
                                    <x-badge :variant="$u->isActive() ? 'income' : 'expense'">
                                        {{ $u->status }}
                                    </x-badge>
                                </td>
                                <td class="font-mono text-[var(--muted)]">
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
