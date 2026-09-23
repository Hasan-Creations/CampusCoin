<x-layouts.guest title="Student Profile Setup">
    <div class="w-full max-w-xl mx-auto">
        <div class="card-campus border hairline-border shadow-sm p-6 sm:p-8">
            <div class="mb-6">
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[4px] bg-[var(--accent-tint)] text-[var(--accent-primary)] text-[11px] font-mono font-semibold uppercase tracking-wider mb-2">
                    <x-icon name="graduation-cap" class="w-3.5 h-3.5" />
                    New Student Onboarding
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">Setup Your Campus Coin Ledger</h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">Configure your academic cohort and spending baselines to unlock tailored budgeting</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-[6px] border border-rose-200 bg-rose-50 dark:bg-rose-950/30 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-400">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ email: '{{ old('email') }}' }">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                        Full Name
                    </label>
                    <input id="name" 
                           type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus 
                           placeholder="Alex Rivera"
                           class="input-campus w-full text-sm">
                </div>

                <!-- Campus Email with .edu indicator -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)]">
                            Campus Email (.edu)
                        </label>
                        <span id="edu-indicator" 
                              class="text-[11px] font-mono font-medium text-[var(--text-muted)]">
                            Institutional email recommended
                        </span>
                    </div>
                    <div class="relative">
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               oninput="checkEduEmail(this.value)"
                               placeholder="alex.rivera@university.edu"
                               class="input-campus w-full text-sm font-mono">
                    </div>
                </div>

                <!-- Academic Year Cohort -->
                <div>
                    <label for="academic_year" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                        Academic Standing / Cohort
                    </label>
                    <select id="academic_year" 
                            name="academic_year" 
                            required 
                            class="input-campus w-full text-sm bg-[var(--bg-surface)]">
                        <option value="" disabled {{ old('academic_year') ? '' : 'selected' }}>Select standing</option>
                        <option value="Freshman" {{ old('academic_year') === 'Freshman' ? 'selected' : '' }}>Freshman (1st Year)</option>
                        <option value="Sophomore" {{ old('academic_year') === 'Sophomore' ? 'selected' : '' }}>Sophomore (2nd Year)</option>
                        <option value="Junior" {{ old('academic_year') === 'Junior' ? 'selected' : '' }}>Junior (3rd Year)</option>
                        <option value="Senior" {{ old('academic_year') === 'Senior' ? 'selected' : '' }}>Senior (4th Year)</option>
                        <option value="Graduate" {{ old('academic_year') === 'Graduate' ? 'selected' : '' }}>Graduate / Master's / PhD</option>
                    </select>
                </div>

                <!-- Financial Baselines Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <!-- Monthly Allowance -->
                    <div>
                        <label for="monthly_allowance" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                            Monthly Allowance / Inflow ($)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-xs font-mono text-[var(--text-muted)]">$</span>
                            <input id="monthly_allowance" 
                                   type="number" 
                                   step="0.01" 
                                   min="0"
                                   name="monthly_allowance" 
                                   value="{{ old('monthly_allowance', '1000.00') }}" 
                                   required 
                                   placeholder="1000.00"
                                   class="input-campus w-full text-sm font-mono pl-7 tabular-nums">
                        </div>
                        <span class="text-[10px] text-[var(--text-muted)] mt-1 block">Stipends, parents, or job baseline</span>
                    </div>

                    <!-- Target Monthly Savings Goal -->
                    <div>
                        <label for="savings_goal" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                            Monthly Savings Target ($)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-xs font-mono text-[var(--text-muted)]">$</span>
                            <input id="savings_goal" 
                                   type="number" 
                                   step="0.01" 
                                   min="0"
                                   name="savings_goal" 
                                   value="{{ old('savings_goal', '200.00') }}" 
                                   required 
                                   placeholder="200.00"
                                   class="input-campus w-full text-sm font-mono pl-7 tabular-nums">
                        </div>
                        <span class="text-[10px] text-[var(--text-muted)] mt-1 block">Target reserve to build monthly</span>
                    </div>
                </div>

                <!-- Password and Confirmation -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                            Password
                        </label>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               placeholder="Minimum 8 characters"
                               class="input-campus w-full text-sm">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5">
                            Confirm Password
                        </label>
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation" 
                               required 
                               placeholder="Repeat password"
                               class="input-campus w-full text-sm">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-2.5 mt-4">
                    <span>Create Profile & Launch Ledger</span>
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </button>
            </form>

            <div class="mt-6 pt-6 border-t hairline-border text-center text-xs text-[var(--text-muted)]">
                Already registered? 
                <a href="{{ route('login') }}" class="text-[var(--accent-primary)] font-semibold hover:underline">
                    Sign in to existing account
                </a>
            </div>
        </div>
    </div>

    <script>
        function checkEduEmail(val) {
            const el = document.getElementById('edu-indicator');
            if (!val) {
                el.innerText = 'Institutional email recommended';
                el.className = 'text-[11px] font-mono font-medium text-[var(--text-muted)]';
                return;
            }
            if (val.toLowerCase().endsWith('.edu') || val.toLowerCase().includes('.edu.')) {
                el.innerText = '✓ Verified Campus Domain';
                el.className = 'text-[11px] font-mono font-medium text-emerald-600 dark:text-emerald-400';
            } else {
                el.innerText = 'Standard domain (non-edu)';
                el.className = 'text-[11px] font-mono font-medium text-[var(--gold)]';
            }
        }
    </script>
</x-layouts.guest>
