<x-layouts.guest title="Administrator Direct Access">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="border-b hairline-border pb-4 text-center">
                <div class="w-10 h-10 bg-[var(--ink)] text-[var(--paper)] mx-auto mb-3 flex items-center justify-center">
                    <x-icon name="lock" class="w-5 h-5 text-[var(--secondary)]" />
                </div>
                <h1 class="font-display text-xl font-medium text-[var(--ink)]">Administrator Direct Access</h1>
                <p class="text-xs text-[var(--muted)] mt-1">Authorized personnel only &bull; Multi-factor audit enabled
                </p>
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

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4"
                aria-label="Administrator Direct Access Form">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-caps text-[var(--muted)] mb-1">
                        Admin Email
                    </label>
                    <x-field id="email" type="email" name="email" autocomplete="email"
                        value="{{ old('email') }}" required autofocus placeholder="admin@campuscoin.edu"
                        class="text-xs font-mono" :hasError="$errors->has('email')" />
                </div>

                <div>
                    <label for="password" class="block text-xs font-caps text-[var(--muted)] mb-1">
                        Root Key / Password
                    </label>
                    <x-field id="password" type="password" name="password" autocomplete="current-password" required
                        placeholder="••••••••••••" class="text-xs" :hasError="$errors->has('password')" />
                </div>

                <x-button variant="primary" type="submit" class="w-full py-3 mt-2">
                    <x-icon name="shield-check" class="w-4 h-4 text-[var(--secondary)]" />
                    <span>Authorize Console Access</span>
                </x-button>
            </form>

            <div class="pt-4 border-t hairline-border text-center text-xs text-[var(--muted)]">
                <a href="{{ route('login') }}"
                    class="hover:text-[var(--ink)] inline-flex items-center gap-1.5 font-mono transition-colors">
                    &larr; Return to Student Portal
                </a>
            </div>

        </div>
    </div>
</x-layouts.guest>
