<?php

namespace App\Services\EmailTemplate;

use App\Models\EmailTemplate;

class EmailTemplateService
{
    public function create(array $data): EmailTemplate
    {
        return EmailTemplate::create($data);
    }

    public function update(
        EmailTemplate $template,
        array $data
    ): EmailTemplate {
        $template->update($data);

        return $template;
    }

    public function delete(
        EmailTemplate $template
    ): void {
        $template->delete();
    }
}