<?php

namespace App\Livewire\EmailTemplates;

use App\Models\EmailTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $templateId): void
    {
        EmailTemplate::findOrFail($templateId)->delete();

        session()->flash(
            'success',
            'Template deleted successfully.'
        );
    }

    public function render()
    {
        return view('livewire.email-templates.index', [
            'templates' => EmailTemplate::query()
                ->when(
                    $this->search,
                    fn ($query) => $query->where(function ($query) {
                        $query
                            ->where('name', 'ilike', "%{$this->search}%")
                            ->orWhere('subject', 'ilike', "%{$this->search}%");
                    })
                )
                ->with('creator')
                ->latest()
                ->paginate(15),
        ]);
    }
}