<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;
use Illuminate\Support\Facades\Mail;

class SendEmailAction implements ActionContract
{
    public function getKey(): string
    {
        return 'send-email';
    }

    public function getLabel(): string
    {
        return 'Send Email';
    }

    public function getConfigSchema(): array
    {
        return [
            'to' => ['type' => 'text', 'label' => 'To', 'required' => true],
            'subject' => ['type' => 'text', 'label' => 'Subject', 'required' => true],
            'body' => ['type' => 'textarea', 'label' => 'Body', 'required' => true],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $to = $input['to'] ?? '';
        $subject = $input['subject'] ?? '';
        $body = $input['body'] ?? '';

        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });

        return ['sent_to' => $to, 'subject' => $subject];
    }
}
