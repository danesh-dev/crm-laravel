<?php

namespace App\Livewire\Tags;

use App\Models\Tag;
use App\Services\Tag\TagService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Edit extends Component
{
    public Tag $tag;

    public string $name = '';

    public function mount(Tag $tag): void
    {
        $this->tag = $tag;
        $this->name = $tag->name;
    }

    public function save(TagService $service): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        try {
            $service->update($this->tag, [
                'name' => $this->name,
            ]);
        } catch (ValidationException $exception) {
            $this->addError('name', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('Tag updated successfully.'));
    }

    public function render(): View
    {
        return view('livewire.tags.edit', [
            'slugPreview' => Str::slug($this->name),
        ]);
    }
}
