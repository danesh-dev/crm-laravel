<?php

namespace App\Services\Email;

use App\Models\EmailTemplate;
use InvalidArgumentException;

class TemplateRenderer
{
    public function renderTemplate(EmailTemplate $template): array
    {
        return [
            'subject' => $this->render($template->subject),
            'body' => $this->render($template->body_html),
        ];
    }

    public function render(string $content): string
    {
        if (preg_match('/{{.*?}}/s', $content)) {
            throw new InvalidArgumentException('Template variables are not supported for automation emails.');
        }

        return $content;
    }
}
