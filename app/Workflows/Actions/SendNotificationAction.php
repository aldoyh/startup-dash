<?php

namespace App\Workflows\Actions;

use App\Models\User;
use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;
use Illuminate\Support\Facades\Notification;

class SendNotificationAction implements ActionContract
{
    public function getKey(): string
    {
        return 'send-notification';
    }

    public function getLabel(): string
    {
        return 'Send Notification';
    }

    public function getConfigSchema(): array
    {
        return [
            'user_id' => ['type' => 'text', 'label' => 'User ID', 'required' => true],
            'message' => ['type' => 'textarea', 'label' => 'Message', 'required' => true],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $user = User::find($input['user_id'] ?? '');

        if (! $user) {
            throw new \RuntimeException('User not found: '.($input['user_id'] ?? 'null'));
        }

        Notification::send($user, new \Illuminate\Notifications\Messages\DatabaseMessage([
            'message' => $input['message'] ?? '',
        ]));

        return ['notified_user' => $user->id, 'message' => $input['message'] ?? ''];
    }
}
