<?php

namespace App\Services\EmailTemplate;

use App\Models\EmailTemplate;
use App\Services\Email\TemplateRenderer;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EmailTemplateService
{
    public function __construct(private TemplateRenderer $renderer) {}

    public function create(array $data): EmailTemplate
    {
        $this->validateStaticContent($data);

        return EmailTemplate::create($data);
    }

    public function update(
        EmailTemplate $template,
        array $data
    ): EmailTemplate {
        $this->validateStaticContent($data);

        $template->update($data);

        return $template;
    }

    public function delete(
        EmailTemplate $template
    ): void {
        $template->delete();
    }

    private function validateStaticContent(array $data): void
    {
        foreach (['subject', 'body_html'] as $field) {
            try {
                $this->renderer->render((string) ($data[$field] ?? ''));
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages([$field => $exception->getMessage()]);
            }
        }
    }
}
