<?php

namespace App\Support;

class TemplateVariables
{
    public static function all(): array
    {
        return [
            '{{name}}',
            '{{email}}',
            '{{signup_date}}',
        ];
    }
}