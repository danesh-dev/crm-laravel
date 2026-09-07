<?php

namespace App\Services\Email;

class TemplatePreviewService
{
    public function preview(string $content): string
    {
        return app(TemplateRenderer::class)
            ->render($content);
    }
}
