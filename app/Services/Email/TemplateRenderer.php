<?php

namespace App\Services\Email;

use App\Models\Customer;

class TemplateRenderer
{
    public function render(
        string $content,
        Customer $customer
    ): string {
        return strtr($content, [
            '{{name}}' => $customer->name,
            '{{email}}' => $customer->email,
            '{{signup_date}}' => optional($customer->signup_at)?->format('Y-m-d') ?? '',
        ]);
    }
}