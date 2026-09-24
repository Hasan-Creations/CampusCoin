<x-layouts.guest title="Administrator Direct Access">
    <div class="w-full max-w-md mx-auto">
        <div class="card-campus border hairline-border shadow-modal p-6 sm:p-8 rounded-[16px]">
            <div class="mb-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-[10px] bg-[var(--text-primary)] text-[var(--bg-surface)] mb-3 shadow-tactile-sm">
                    <x-icon name="lock" class="w-5 h-5 text-[var(--gold)]" />
                </div>
                <h1 class="font-heading text-xl font-bold text-[var(--text-primary)]">Administrator Direct Access</h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">Authorized personnel only &bull; Multi-factor audit enabled</p>
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

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4" aria-label="Administrator Direct Access Form">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5 font-mono">
                        Admin Email
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           autocomplete="email"
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           placeholder="admin@campuscoin.edu"
                           class="input-campus w-full text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] mb-1.5 font-mono">
                        Root Key / Password
                    </label>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           autocomplete="current-password"
                           required 
                           placeholder="••••••••••••"
                           class="input-campus w-full text-sm">
                </div>

                <button type="submit" class="btn-primary w-full py-2.5 mt-2 bg-[var(--text-primary)] text-[var(--bg-surface)] hover:bg-[var(--text-muted)]">
                    <x-icon name="shield-check" class="w-4 h-4 text-[var(--gold)]" />
                    <span>Authorize Console Access</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t hairline-border text-center text-xs text-[var(--text-muted)]">
                <a href="{{ route('login') }}" class="hover:text-[var(--text-primary)] inline-flex items-center gap-1.5 font-mono transition-colors">
                    &larr; Return to Student Portal
                </a>
            </div>

            <!-- Credentials Hint -->
            <div class="mt-4 p-3 rounded-[10px] bg-[var(--bg-subtle)] text-[11px] font-mono text-[var(--text-muted)] space-y-0.5 border hairline-border">
                <div class="font-semibold text-[var(--text-primary)]">Admin Credentials:</div>
                <div>User: <span class="text-[var(--gold)] font-medium">admin@campuscoin.edu</span></div>
                <div>Pass: <span class="text-[var(--gold)] font-medium">AdminSecure123!</span></div>
            </div>
        </div>
    </div>
</x-layouts.guest>
