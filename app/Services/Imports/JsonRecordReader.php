<?php

namespace App\Services\Imports;

use Generator;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

class JsonRecordReader
{
    public function count(string $path): int
    {
        $count = 0;

        foreach ($this->records($path) as $_) {
            $count++;
        }

        return $count;
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function records(string $path): Generator
    {
        $handle = fopen($this->resolvePath($path), 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open import file.');
        }

        $row = 0;
        $depth = 0;
        $buffer = '';
        $capturing = false;
        $inString = false;
        $escaped = false;
        $seenArray = false;

        while (($chunk = fread($handle, 1024 * 64)) !== false && $chunk !== '') {
            $length = strlen($chunk);

            for ($i = 0; $i < $length; $i++) {
                $char = $chunk[$i];

                if (! $seenArray) {
                    if (ctype_space($char)) {
                        continue;
                    }

                    if ($char !== '[') {
                        fclose($handle);

                        throw new RuntimeException('JSON import must be a top-level array.');
                    }

                    $seenArray = true;

                    continue;
                }

                if (! $capturing && $char !== '{') {
                    continue;
                }

                $capturing = true;
                $buffer .= $char;

                if ($escaped) {
                    $escaped = false;

                    continue;
                }

                if ($char === '\\' && $inString) {
                    $escaped = true;

                    continue;
                }

                if ($char === '"') {
                    $inString = ! $inString;

                    continue;
                }

                if ($inString) {
                    continue;
                }

                if ($char === '{') {
                    $depth++;
                }

                if ($char === '}') {
                    $depth--;
                }

                if ($capturing && $depth === 0) {
                    $row++;

                    try {
                        yield $row => json_decode($buffer, true, flags: JSON_THROW_ON_ERROR);
                    } catch (JsonException $exception) {
                        fclose($handle);

                        throw new RuntimeException('Invalid JSON object at row '.$row.': '.$exception->getMessage());
                    }

                    $buffer = '';
                    $capturing = false;
                }
            }
        }

        fclose($handle);

        if (! $seenArray) {
            throw new RuntimeException('JSON import must be a top-level array.');
        }

        if ($capturing || $depth !== 0 || $inString) {
            throw new RuntimeException('JSON import is incomplete or malformed.');
        }
    }

    private function resolvePath(string $path): string
    {
        return is_file($path)
            ? $path
            : Storage::path($path);
    }
}
