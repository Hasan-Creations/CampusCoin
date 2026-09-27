<x-layouts.guest title="Password Recovery">
    <div class="w-full max-w-xl mx-auto">
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="border-b hairline-border pb-4">
                <span class="text-xs font-caps text-[var(--muted)]">Account Access</span>
                <h1 class="font-display text-2xl font-medium text-[var(--ink)] mt-1">Reset Your Password</h1>
                <p class="text-xs text-[var(--muted)] mt-1">Enter your account email and we will send a secure reset link.</p>
            </div>

            @if (session('status'))
                <div role="status" class="p-3.5 border border-[var(--accent)] text-xs text-[var(--accent)]">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div role="alert" class="p-3.5 border border-[var(--expense)] text-xs text-[var(--expense)]">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-caps text-[var(--muted)] mb-1">Campus Email</label>
                    <x-field id="email" type="email" name="email" autocomplete="email" value="{{ old('email') }}" required class="text-xs font-mono" :hasError="$errors->has('email')" />
                </div>
                <x-button variant="accent" type="submit" class="w-full py-3">Send Password Reset Link</x-button>
            </form>

            <div class="pt-4 border-t hairline-border text-center text-xs text-[var(--muted)]">
                <a href="{{ route('login') }}" class="text-[var(--accent)] font-medium hover:underline">Return to sign in</a>
            </div>
        </div>
    </div>
</x-layouts.guest>