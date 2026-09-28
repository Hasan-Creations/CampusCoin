<x-layouts.guest title="Choose a New Password">
    <div class="w-full max-w-xl mx-auto">
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="border-b hairline-border pb-4">
                <span class="text-xs font-caps text-[var(--muted)]">Account Access</span>
                <h1 class="font-display text-2xl font-medium text-[var(--ink)] mt-1">Choose a New Password</h1>
            </div>

            @if ($errors->any())
                <div role="alert" class="p-3.5 border border-[var(--expense)] text-xs text-[var(--expense)]">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="block text-xs font-caps text-[var(--muted)] mb-1">Campus Email</label>
                    <x-field id="email" type="email" name="email" autocomplete="email"
                        value="{{ old('email', $email) }}" required class="text-xs font-mono" :hasError="$errors->has('email')" />
                </div>
                <div>
                    <label for="password" class="block text-xs font-caps text-[var(--muted)] mb-1">New Password</label>
                    <x-field id="password" type="password" name="password" autocomplete="new-password" required
                        class="text-xs" :hasError="$errors->has('password')" />
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-caps text-[var(--muted)] mb-1">Confirm
                        New Password</label>
                    <x-field id="password_confirmation" type="password" name="password_confirmation"
                        autocomplete="new-password" required class="text-xs" />
                </div>
                <x-button variant="accent" type="submit" class="w-full py-3">Update Password</x-button>
            </form>
        </div>
    </div>
</x-layouts.guest>
