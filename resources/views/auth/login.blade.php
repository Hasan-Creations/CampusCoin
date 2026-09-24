<x-layouts.guest title="Student Sign In">
    <div class="w-full max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- 60% Column: Value Proposition & System Proof Metrics -->
        <div class="lg:col-span-7 space-y-8 pr-0 lg:pr-8">
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-[6px] border hairline-border bg-[var(--bg-subtle)] text-xs font-mono text-[var(--accent-primary)] font-semibold tracking-wide shadow-tactile-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent-primary)]"></span>
                    CAMPUS FINANCIAL INTELLIGENCE
                </div>
                <h1 class="font-heading text-3xl sm:text-4xl font-bold tracking-tight text-[var(--text-primary)] leading-tight">
                    Smart Spending, <br><span class="text-[var(--accent-primary)]">Student Style.</span>
                </h1>
                <p class="text-sm sm:text-base text-[var(--text-muted)] leading-relaxed max-w-xl">
                    Engineered for collegiate cash flow. Reconcile monthly allowances, enforce category spending limits, and track savings goals with deterministic precision.
                </p>
            </div>

            <!-- System Proof Metrics Grid -->
            <div class="grid grid-cols-3 gap-4 pt-2 border-t hairline-border">
                <div class="p-4 rounded-[10px] border hairline-border bg-[var(--bg-surface)] shadow-tactile-sm">
                    <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Accuracy</div>
                    <div class="font-mono text-xl font-bold text-[var(--text-primary)] mt-1 tabular-nums">100%</div>
                    <div class="text-[11px] text-[var(--text-muted)] mt-0.5">Fixed-point arithmetic</div>
                </div>

                <div class="p-4 rounded-[10px] border hairline-border bg-[var(--bg-surface)] shadow-tactile-sm">
                    <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Isolation</div>
                    <div class="font-mono text-xl font-bold text-[var(--accent-primary)] mt-1">Strict</div>
                    <div class="text-[11px] text-[var(--text-muted)] mt-0.5">Tenant privacy safe</div>
                </div>

                <div class="p-4 rounded-[10px] border hairline-border bg-[var(--bg-surface)] shadow-tactile-sm">
                    <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Advisory</div>
                    <div class="font-mono text-xl font-bold text-[var(--gold)] mt-1">Controlled</div>
                    <div class="text-[11px] text-[var(--text-muted)] mt-0.5">Student override authority</div>
                </div>
            </div>

            <!-- Features Bullets with SVG icons -->
            <div class="space-y-2.5 text-xs text-[var(--text-muted)]">
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent-primary)] flex-shrink-0" />
                    <span>Real-time budget alerts at 75% and 100% deterministic thresholds</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent-primary)] flex-shrink-0" />
                    <span>Double-entry cash flow ledger with automatic category aggregation</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent-primary)] flex-shrink-0" />
                    <span>CSV statement import with duplicate safety validation</span>
                </div>
            </div>
        </div>

        <!-- 40% Column: Login Card -->
        <div class="lg:col-span-5">
            <div class="card-campus border hairline-border shadow-modal p-6 sm:p-8 rounded-[16px]">
                <div class="mb-6">
                    <h2 class="font-heading text-xl font-bold text-[var(--text-primary)]">Student Sign In</h2>
                    <p class="text-xs text-[var(--text-muted)] mt-1">Access your campus spending ledger and budgets</p>
                </div>

                @if ($errors->any())
                    <div role="alert" aria-live="assertive" class="mb-4 p-3.5 rounded-[10px] border border-rose-200 bg-rose-50 dark:bg-rose-950/30 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-400">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4" aria-label="Student Sign In Form">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5 font-mono">
                            Campus Email
                        </label>
                        <div class="relative">
                            <input id="email" 
                                   type="email" 
                                   name="email" 
                                   autocomplete="email"
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   placeholder="alex.rivera@campus.edu"
                                   class="input-campus w-full text-sm">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] font-mono">
                                Password
                            </label>
                        </div>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               autocomplete="current-password"
                               required 
                               placeholder="••••••••••••"
                               class="input-campus w-full text-sm">
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-[var(--text-muted)]">
                            <input type="checkbox" name="remember" class="rounded-[4px] border-[var(--border-hairline)] text-[var(--accent-primary)] focus:ring-0">
                            <span>Remember session</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full py-2.5 mt-2">
                        <span>Sign In to Campus Coin</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t hairline-border text-center text-xs text-[var(--text-muted)]">
                    New student? 
                    <a href="{{ route('register') }}" class="text-[var(--accent-primary)] font-semibold hover:underline">
                        Create an account
                    </a>
                </div>

                <!-- Test credentials quick reminder -->
                <div class="mt-4 p-3 rounded-[10px] bg-[var(--bg-subtle)] text-[11px] font-mono text-[var(--text-muted)] space-y-0.5 border hairline-border">
                    <div class="font-semibold text-[var(--text-primary)]">Demo Account:</div>
                    <div>User: <span class="text-[var(--accent-primary)] font-medium">alex.rivera@campus.edu</span></div>
                    <div>Pass: <span class="text-[var(--accent-primary)] font-medium">StudentSecure123!</span></div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
