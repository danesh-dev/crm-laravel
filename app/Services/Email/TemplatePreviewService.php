<?php

namespace App\Services\Email;

use App\Models\Customer;

class TemplatePreviewService
{
    public function preview(
        string $content,
        Customer $customer
    ): string {
        return app(TemplateRenderer::class)
            ->render($content, $customer);
    }
}