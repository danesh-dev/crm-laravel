<?php

namespace App\Services\Tag;

use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TagService
{
    public function create(array $data): Tag
    {
        $slug = $this->slug($data['name']);
        $this->ensureSlugIsAvailable($slug);

        return Tag::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);
    }

    public function update(Tag $tag, array $data): Tag
    {
        $slug = $this->slug($data['name']);
        $this->ensureSlugIsAvailable($slug, $tag);

        $tag->update([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        if ($tag->customers()->exists()) {
            throw ValidationException::withMessages([
                'tag' => __('Tags assigned to customers cannot be deleted.'),
            ]);
        }

        $tag->delete();
    }

    private function slug(string $name): string
    {
        return Str::slug($name);
    }

    private function ensureSlugIsAvailable(string $slug, ?Tag $ignore = null): void
    {
        if ($slug === '') {
            throw ValidationException::withMessages([
                'name' => __('The tag name must contain at least one letter or number.'),
            ]);
        }

        $exists = Tag::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->when($ignore, fn ($query): mixed => $query->whereKeyNot($ignore->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => __('A tag with this slug already exists.'),
            ]);
        }
    }
}
