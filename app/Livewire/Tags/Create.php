<?php

namespace App\Livewire\Tags;

use App\Services\Tag\TagService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public function save(TagService $service): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ], [], [
            'name' => __('name'),
        ]);

        try {
            $service->create([
                'name' => $this->name,
            ]);
        } catch (ValidationException $exception) {
            $this->addError('name', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('Tag created successfully.'));

        $this->redirectRoute('tags.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tags.create', [
            'slugPreview' => Str::slug($this->name),
        ]);
    }
}
