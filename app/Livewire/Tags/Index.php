<?php

namespace App\Livewire\Tags;

use App\Models\Tag;
use App\Services\Tag\TagService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $message = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $tagId, TagService $service): void
    {
        try {
            $service->delete(Tag::query()->findOrFail($tagId));

            session()->flash('success', __('Tag deleted successfully.'));
        } catch (ValidationException $exception) {
            $this->message = collect($exception->errors())->flatten()->first();
        }
    }

    public function render(): View
    {
        return view('livewire.tags.index', [
            'tags' => Tag::query()
                ->withCount('customers')
                ->when($this->search, function ($query): void {
                    $query->where(function ($query): void {
                        $query
                            ->where('name', 'ilike', "%{$this->search}%")
                            ->orWhere('slug', 'ilike', "%{$this->search}%");
                    });
                })
                ->orderBy('name')
                ->paginate(15),
        ]);
    }
}
