<div class="space-y-6">
    @if ($feedbackMessage)
        <div role="status" class="p-4 border border-[var(--accent)] text-xs text-[var(--accent)]">{{ $feedbackMessage }}</div>
    @endif
    @if ($errorMessage)
        <div role="alert" class="p-4 border border-[var(--expense)] text-xs text-[var(--expense)]">{{ $errorMessage }}</div>
    @endif

    <div class="pb-5 border-b hairline-border">
        <span class="text-xs text-[var(--muted)] font-mono">Platform Governance</span>
        <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] mt-1 headline-rule">Campus Tips & Announcements</h1>
        <p class="text-xs text-[var(--muted)] mt-1">Manage active messages shown to students on their dashboard.</p>
    </div>

    <form wire:submit="saveTemplate" class="bg-[var(--panel)] border hairline-border p-5 sm:p-6 space-y-4">
        <h2 class="font-display text-base font-medium text-[var(--ink)]">{{ $editingId ? 'Edit Campus Update' : 'New Campus Update' }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="template-type" class="block text-xs font-caps text-[var(--muted)] mb-1">Type</label>
                <x-field id="template-type" type="select" wire:model="type" class="text-xs">
                    <option value="tip">Tip</option>
                    <option value="announcement">Announcement</option>
                </x-field>
                @error('type') <span class="text-xs text-[var(--expense)]">{{ $message }}</span> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="template-title" class="block text-xs font-caps text-[var(--muted)] mb-1">Title</label>
                <x-field id="template-title" type="text" wire:model="title" class="text-xs" />
                @error('title') <span class="text-xs text-[var(--expense)]">{{ $message }}</span> @enderror
            </div>
        </div>
        <div>
            <label for="template-message" class="block text-xs font-caps text-[var(--muted)] mb-1">Message</label>
            <x-field id="template-message" type="textarea" rows="3" wire:model="message" class="text-xs" />
            @error('message') <span class="text-xs text-[var(--expense)]">{{ $message }}</span> @enderror
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t hairline-border">
            <label class="flex items-center gap-2 text-xs text-[var(--muted)]">
                <input type="checkbox" wire:model="is_active" class="border-[var(--hairline)] text-[var(--accent)] focus:ring-0">
                Show to students
            </label>
            <div class="flex items-center gap-2">
                @if ($editingId)
                    <x-button variant="secondary" type="button" wire:click="cancelEdit">Cancel</x-button>
                @endif
                <x-button variant="accent" type="submit">{{ $editingId ? 'Save Changes' : 'Add Update' }}</x-button>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto border hairline-border">
        <table class="ledger-table w-full text-xs text-left">
            <thead>
                <tr>
                    <th scope="col">Type</th>
                    <th scope="col">Title and Message</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $template)
                    <tr class="table-row-tactile">
                        <td class="font-mono text-[var(--muted)]">{{ ucfirst($template->type) }}</td>
                        <td>
                            <div class="font-medium text-[var(--ink)]">{{ $template->title }}</div>
                            <div class="text-[var(--muted)] mt-1">{{ $template->message }}</div>
                        </td>
                        <td>{{ $template->is_active ? 'Active' : 'Hidden' }}</td>
                        <td class="text-right whitespace-nowrap">
                            <button type="button" wire:click="editTemplate({{ $template->id }})" class="btn-icon !w-7 !h-7" aria-label="Edit {{ $template->title }}">
                                <x-icon name="edit" class="w-3.5 h-3.5" />
                            </button>
                            <button type="button" wire:click="deleteTemplate({{ $template->id }})" wire:confirm="Remove this campus update?" class="btn-icon !w-7 !h-7 text-[var(--expense)]" aria-label="Delete {{ $template->title }}">
                                <x-icon name="trash-2" class="w-3.5 h-3.5" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-xs text-[var(--muted)]">No campus updates have been added.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>