<?php

namespace App\Services\Imports\DTOs;

readonly class ImportError
{
    public function __construct(
        public int $row,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'row' => $this->row,
            'message' => $this->message,
        ];
    }
}
