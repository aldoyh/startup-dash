<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;
use Illuminate\Support\Facades\Http;

class HttpRequestAction implements ActionContract
{
    public function getKey(): string
    {
        return 'http-request';
    }

    public function getLabel(): string
    {
        return 'HTTP Request';
    }

    public function getConfigSchema(): array
    {
        return [
            'method' => ['type' => 'select', 'label' => 'Method', 'options' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], 'required' => true],
            'url' => ['type' => 'text', 'label' => 'URL', 'required' => true],
            'headers' => ['type' => 'key-value', 'label' => 'Headers'],
            'body' => ['type' => 'textarea', 'label' => 'Body (JSON)'],
            'timeout' => ['type' => 'number', 'label' => 'Timeout (seconds)', 'default' => 30],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $method = strtolower($input['method'] ?? 'get');
        $url = $input['url'] ?? '';
        $headers = $input['headers'] ?? [];
        $body = $input['body'] ?? null;
        $timeout = (int) ($input['timeout'] ?? 30);

        $request = Http::timeout($timeout)->withHeaders($headers);

        if ($body && in_array($method, ['post', 'put', 'patch'])) {
            $decoded = is_string($body) ? json_decode($body, true) : $body;
            $response = $request->$method($url, $decoded ?? []);
        } else {
            $response = $request->$method($url);
        }

        return [
            'status_code' => $response->status(),
            'body' => $response->json() ?? $response->body(),
            'headers' => $response->headers(),
        ];
    }
}
