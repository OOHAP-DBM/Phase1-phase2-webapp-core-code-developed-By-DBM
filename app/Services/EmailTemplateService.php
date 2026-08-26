<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Blade;

class EmailTemplateService
{
    /**
     * Render an email template with actual values.
     */
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

    /**
     * Replace {{ placeholder }} values.
     */
    protected function replaceVariables(
        string $content,
        array $variables
    ): string {
        foreach ($variables as $key => $value) {

            $value = $value ?? '';

            $content = str_replace(
                [
                    '{{' . $key . '}}',
                    '{{ ' . $key . ' }}',
                ],
                (string) $value,
                $content
            );
        }

        return $content;
    }
}