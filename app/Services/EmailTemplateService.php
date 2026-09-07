<?php

namespace App\Services;

use App\Models\EmailTemplate;

class EmailTemplateService
{
    public function render(string $key, array $variables = []): array
    {
        $template = EmailTemplate::where('key', $key)
            ->where('is_active', true)
            ->firstOrFail();

        $subject = $this->replaceVariables(
            $template->subject,
            $variables
        );

        $body = $this->replaceVariables(
            $template->body,
            $variables
        );

        return [
            'template' => $template,
            'subject' => $subject,
            'body' => $body,
        ];
    }

    protected function replaceVariables(
        string $content,
        array $variables
    ): string {
        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $value = $value ?? '';

            $content = preg_replace(
                '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/',
                e((string) $value),
                $content
            );
        }

        return $content;
    }
}