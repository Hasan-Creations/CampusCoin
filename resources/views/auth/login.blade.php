<x-layouts.guest title="Student Sign In">
    <div class="w-full max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        <!-- Value Proposition Column -->
        <div class="lg:col-span-7 space-y-8 pr-0 lg:pr-8">
            <div class="space-y-4">
                <span class="text-xs font-caps text-[var(--muted)]">
                    CAMPUS FINANCIAL INTELLIGENCE
                </span>
                <h1
                    class="font-display text-3xl sm:text-4xl font-medium tracking-tight text-[var(--ink)] leading-tight headline-rule">
                    Smart Spending, <br>
                    Student Style.
                </h1>
                <p class="text-sm sm:text-base text-[var(--muted)] leading-relaxed max-w-xl pt-2">
                    Track allowances, category limits, and savings goals with precise calculations.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="p-4 bg-[var(--panel)]">
                    <div class="text-xs font-caps text-[var(--muted)]">Accuracy</div>
                    <div class="font-mono text-xl font-medium text-[var(--ink)] mt-1 tabular-nums">100%</div>
                    <div class="text-[11px] text-[var(--muted)] mt-0.5">Fixed-point arithmetic</div>
                </div>

                <div class="p-4 bg-[var(--panel)]">
                    <div class="text-xs font-caps text-[var(--muted)]">Isolation</div>
                    <div class="font-mono text-xl font-medium text-[var(--accent)] mt-1">Strict</div>
                    <div class="text-[11px] text-[var(--muted)] mt-0.5">Tenant privacy safe</div>
                </div>

                <div class="p-4 bg-[var(--panel)]">
                    <div class="text-xs font-caps text-[var(--muted)]">Advisory</div>
                    <div class="font-mono text-xl font-medium text-[var(--secondary)] mt-1">Controlled</div>
                    <div class="text-[11px] text-[var(--muted)] mt-0.5">Student override authority</div>
                </div>
            </div>

            <div class="space-y-2.5 text-xs text-[var(--muted)]">
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent)] flex-shrink-0" />
                    <span>Real-time budget alerts at 75% and 100% deterministic thresholds</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent)] flex-shrink-0" />
                    <span>Double-entry cash flow ledger with automatic category aggregation</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-icon name="check-circle-2" class="w-4 h-4 text-[var(--accent)] flex-shrink-0" />
                    <span>CSV statement import with duplicate safety validation</span>
                </div>
            </div>
        </div>
        <div class="lg:col-span-5">
            <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
                <div class="border-b hairline-border pb-4">
                    <h2 class="font-display text-xl font-medium text-[var(--ink)]">Student Sign In</h2>
                    <p class="text-xs text-[var(--muted)] mt-1">Access your campus spending ledger and budgets</p>
                </div>

                @if ($errors->any())
                    <div role="alert" aria-live="assertive"
                        class="p-3.5 border border-[var(--expense)] bg-[var(--paper)] text-xs text-[var(--expense)]">
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
                        <label for="email" class="block text-xs font-caps text-[var(--muted)] mb-1">
                            Campus Email
                        </label>
                        <x-field id="email" type="email" name="email" autocomplete="email"
                            value="{{ old('email') }}" required autofocus placeholder="alex.rivera@campus.edu"
                            class="text-xs font-mono" :hasError="$errors->has('email')" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-xs font-caps text-[var(--muted)]">
                                Password
                            </label>
                            <a href="{{ route('password.request') }}"
                                class="text-[11px] text-[var(--accent)] hover:underline">
                                Forgot password?
                            </a>
                        </div>
                        <x-field id="password" type="password" name="password" autocomplete="current-password" required
                            placeholder="••••••••••••" class="text-xs" :hasError="$errors->has('password')" />
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-[var(--muted)]">
                            <input type="checkbox" name="remember"
                                class="border-[var(--hairline)] text-[var(--accent)] focus:ring-0">
                            <span>Remember session</span>
                        </label>
                    </div>

                    <x-button variant="accent" type="submit" class="w-full py-3">
                        <span>Sign In to Campus Coin</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </x-button>
                </form>

                <div class="pt-4 border-t hairline-border text-center text-xs text-[var(--muted)]">
                    New student?
                    <a href="{{ route('register') }}" class="text-[var(--accent)] font-medium hover:underline">
                        Create an account
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-layouts.guest>
