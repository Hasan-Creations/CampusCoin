<x-layouts.guest title="Student Profile Setup">
    <div class="w-full max-w-xl mx-auto">
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="border-b hairline-border pb-4">
                <span class="text-xs font-caps text-[var(--muted)]">Student Onboarding</span>
                <h1 class="font-display text-2xl font-medium text-[var(--ink)] mt-1">Setup Your Campus Coin Ledger</h1>
                <p class="text-xs text-[var(--muted)] mt-1">Choose your academic cohort and enter your monthly spending details to set up a budget.</p>
            </div>

            @if ($errors->any())
                <div role="alert" aria-live="assertive" class="p-3.5 border border-[var(--expense)] bg-[var(--paper)] text-xs text-[var(--expense)]">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4" aria-label="Student Registration Form" x-data="{ email: '{{ old('email') }}' }">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-caps text-[var(--muted)] mb-1">
                        Full Name
                    </label>
                    <x-field id="name" 
                             type="text" 
                             name="name" 
                             autocomplete="name" 
                             value="{{ old('name') }}" 
                             required 
                             autofocus 
                             placeholder="Alex Rivera" 
                             class="text-xs" 
                             :hasError="$errors->has('name')" />
                </div>

                <!-- Campus Email with .edu indicator -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="email" class="block text-xs font-caps text-[var(--muted)]">
                            Campus Email (.edu)
                        </label>
                        <span id="edu-indicator" 
                              class="text-[11px] font-mono text-[var(--muted)]">
                            Institutional email recommended
                        </span>
                    </div>
                    <x-field id="email" 
                             type="email" 
                             name="email" 
                             autocomplete="email" 
                             value="{{ old('email') }}" 
                             required 
                             oninput="checkEduEmail(this.value)" 
                             placeholder="alex.rivera@university.edu" 
                             class="text-xs font-mono" 
                             :hasError="$errors->has('email')" />
                </div>

                <!-- Academic Year Cohort -->
                <div>
                    <label for="academic_year" class="block text-xs font-caps text-[var(--muted)] mb-1">
                        Academic Standing / Cohort
                    </label>
                    <x-field type="select" 
                             id="academic_year" 
                             name="academic_year" 
                             required 
                             class="text-xs"
                             :hasError="$errors->has('academic_year')">
                        <option value="" disabled {{ old('academic_year') ? '' : 'selected' }}>Select standing</option>
                        <option value="Freshman" {{ old('academic_year') === 'Freshman' ? 'selected' : '' }}>Freshman (1st Year)</option>
                        <option value="Sophomore" {{ old('academic_year') === 'Sophomore' ? 'selected' : '' }}>Sophomore (2nd Year)</option>
                        <option value="Junior" {{ old('academic_year') === 'Junior' ? 'selected' : '' }}>Junior (3rd Year)</option>
                        <option value="Senior" {{ old('academic_year') === 'Senior' ? 'selected' : '' }}>Senior (4th Year)</option>
                        <option value="Graduate" {{ old('academic_year') === 'Graduate' ? 'selected' : '' }}>Graduate / Master's / PhD</option>
                    </x-field>
                </div>

                <!-- Financial Baselines Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <!-- Monthly Allowance -->
                    <div>
                        <label for="monthly_allowance" class="block text-xs font-caps text-[var(--muted)] mb-1">
                            Monthly Allowance / Inflow ($)
                        </label>
                        <x-field id="monthly_allowance" 
                                 type="number" 
                                 numeric
                                 step="0.01" 
                                 min="0" 
                                 name="monthly_allowance" 
                                 value="{{ old('monthly_allowance', '1000.00') }}" 
                                 required 
                                 placeholder="1000.00" 
                                 class="text-xs font-mono tabular-nums"
                                 :hasError="$errors->has('monthly_allowance')" />
                        <span class="text-[11px] text-[var(--muted)] mt-1 block">Baseline family living allowance</span>
                    </div>

                    <!-- Target Monthly Savings Goal -->
                    <div>
                        <label for="savings_goal" class="block text-xs font-caps text-[var(--muted)] mb-1">
                            Monthly Savings Target ($)
                        </label>
                        <x-field id="savings_goal" 
                                 type="number" 
                                 numeric
                                 step="0.01" 
                                 min="0" 
                                 name="savings_goal" 
                                 value="{{ old('savings_goal', '200.00') }}" 
                                 required 
                                 placeholder="200.00" 
                                 class="text-xs font-mono tabular-nums"
                                 :hasError="$errors->has('savings_goal')" />
                        <span class="text-[11px] text-[var(--muted)] mt-1 block">Target reserve to build monthly</span>
                    </div>
                </div>

                <!-- Password and Confirmation -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label for="password" class="block text-xs font-caps text-[var(--muted)] mb-1">
                            Password
                        </label>
                        <x-field id="password" 
                                 type="password" 
                                 name="password" 
                                 autocomplete="new-password" 
                                 required 
                                 placeholder="Min. 8 characters" 
                                 class="text-xs"
                                 :hasError="$errors->has('password')" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-caps text-[var(--muted)] mb-1">
                            Confirm Password
                        </label>
                        <x-field id="password_confirmation" 
                                 type="password" 
                                 name="password_confirmation" 
                                 autocomplete="new-password" 
                                 required 
                                 placeholder="Repeat password" 
                                 class="text-xs" />
                    </div>
                </div>

                <x-button variant="accent" type="submit" class="w-full py-3 mt-4">
                    <span>Create Profile & Launch Ledger</span>
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </x-button>
            </form>

            <div class="pt-4 border-t hairline-border text-center text-xs text-[var(--muted)]">
                Already registered? 
                <a href="{{ route('login') }}" class="text-[var(--accent)] font-medium hover:underline">
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
                el.className = 'text-[11px] font-mono text-[var(--muted)]';
                return;
            }
            if (val.toLowerCase().endsWith('.edu') || val.toLowerCase().includes('.edu.')) {
                el.innerText = '✓ Verified Campus Domain';
                el.className = 'text-[11px] font-mono text-[var(--accent)]';
            } else {
                el.innerText = 'Standard domain (non-edu)';
                el.className = 'text-[11px] font-mono text-[var(--secondary)]';
            }
        }
    </script>
</x-layouts.guest>
