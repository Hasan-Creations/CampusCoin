<?php

namespace App\Livewire\Admin;

use App\Models\SystemTipTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SystemTipTemplateManager extends Component
{
    public ?int $editingId = null;

    public string $title = '';

    public string $message = '';

    public string $type = 'tip';

    public bool $is_active = true;

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    public function boot(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized. Administrator privileges required.');
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:tip,announcement'],
            'is_active' => ['boolean'],
        ];
    }

    public function editTemplate(int $id): void
    {
        $template = SystemTipTemplate::find($id);

        if (! $template) {
            $this->errorMessage = 'Campus tip or announcement not found.';

            return;
        }

        $this->editingId = $template->id;
        $this->title = $template->title;
        $this->message = $template->message;
        $this->type = $template->type;
        $this->is_active = $template->is_active;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'title', 'message', 'type', 'is_active']);
        $this->type = 'tip';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function saveTemplate(): void
    {
        $this->validate();

        $values = [
            'title' => trim($this->title),
            'message' => trim($this->message),
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $template = SystemTipTemplate::find($this->editingId);

            if (! $template) {
                $this->errorMessage = 'Campus tip or announcement not found.';

                return;
            }

            $template->update($values);
            $this->feedbackMessage = 'Campus update saved.';
        } else {
            SystemTipTemplate::create($values);
            $this->feedbackMessage = 'Campus update created.';
        }

        $this->reset(['editingId', 'title', 'message', 'type', 'is_active']);
        $this->type = 'tip';
        $this->is_active = true;
    }

    public function deleteTemplate(int $id): void
    {
        $template = SystemTipTemplate::find($id);

        if (! $template) {
            $this->errorMessage = 'Campus tip or announcement not found.';

            return;
        }

        $template->delete();
        $this->feedbackMessage = 'Campus update removed.';

        if ($this->editingId === $id) {
            $this->reset(['editingId', 'title', 'message', 'type', 'is_active']);
            $this->type = 'tip';
            $this->is_active = true;
        }
    }

    public function render()
    {
        return view('livewire.admin.system-tip-template-manager', [
            'templates' => SystemTipTemplate::orderBy('type')->orderBy('title')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Campus Tip Templates']);
    }
}
