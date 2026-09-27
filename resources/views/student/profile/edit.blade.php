<x-layouts.app title="Student Profile">
    <x-slot:header>Profile</x-slot:header>

    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] headline-rule">Student Profile</h1>
            <p class="text-xs text-[var(--muted)] mt-1.5">Update your account details and monthly financial baselines.</p>
        </div>

        @if (session('status'))
            <div role="status" class="p-4 border border-[var(--accent)] text-xs text-[var(--accent)]">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="p-4 border border-[var(--expense)] text-xs text-[var(--expense)]">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-xs font-caps text-[var(--muted)] mb-1">Full Name</label>
                <x-field id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required class="text-xs" :hasError="$errors->has('name')" />
            </div>

            <div>
                <label for="academic_year" class="block text-xs font-caps text-[var(--muted)] mb-1">Academic Year</label>
                <x-field id="academic_year" name="academic_year" type="select" required class="text-xs" :hasError="$errors->has('academic_year')">
                    @foreach (['Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate'] as $year)
                        <option value="{{ $year }}" @selected(old('academic_year', $user->academic_year) === $year)>{{ $year }}</option>
                    @endforeach
                </x-field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="monthly_allowance" class="block text-xs font-caps text-[var(--muted)] mb-1">Monthly Allowance ($)</label>
                    <x-field id="monthly_allowance" name="monthly_allowance" type="number" numeric step="0.01" min="0" max="999999.99" value="{{ old('monthly_allowance', $user->monthly_allowance) }}" required class="text-xs font-mono" :hasError="$errors->has('monthly_allowance')" />
                </div>
                <div>
                    <label for="savings_goal" class="block text-xs font-caps text-[var(--muted)] mb-1">Monthly Savings Goal ($)</label>
                    <x-field id="savings_goal" name="savings_goal" type="number" numeric step="0.01" min="0" max="999999.99" value="{{ old('savings_goal', $user->savings_goal) }}" required class="text-xs font-mono" :hasError="$errors->has('savings_goal')" />
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 border-t hairline-border">
                <a href="{{ route('dashboard') }}" class="text-xs text-[var(--muted)] hover:text-[var(--ink)]">Cancel</a>
                <x-button variant="accent" type="submit">Save Profile</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>