<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Contracts\ActionContract;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;

class PromofyAction implements ActionContract
{
    public function getKey(): string
    {
        return 'promofy';
    }

    public function getLabel(): string
    {
        return 'PROMOFY';
    }

    public function getConfigSchema(): array
    {
        return [
            'source_type' => [
                'type' => 'select',
                'label' => 'Source Type',
                'options' => [
                    'file_path' => 'Local Video File',
                    'youtube_video_id' => 'YouTube Video ID',
                    'youtube_channel_id' => 'YouTube Channel ID',
                ],
                'required' => true,
            ],
            'source_value' => ['type' => 'text', 'label' => 'Source Value', 'required' => true],
            'transcript_json' => [
                'type' => 'textarea',
                'label' => 'Transcript JSON',
                'placeholder' => '[{"start":0,"end":10,"text":"..."}]',
                'helperText' => 'For channel mode, include video_id in each entry.',
                'required' => true,
            ],
            'analysis_prompt' => [
                'type' => 'textarea',
                'label' => 'Extra Analysis Prompt',
                'placeholder' => 'Optional additional instructions for LLM analysis.',
            ],
            'llm_response_json' => [
                'type' => 'textarea',
                'label' => 'LLM JSON Response',
                'placeholder' => '{"clips":[{"start":5,"end":20,"reason":"..."}]}',
                'helperText' => 'Optional. If omitted, PROMOFY uses transcript heuristics.',
            ],
            'max_clips' => ['type' => 'number', 'label' => 'Max Clips', 'default' => 6],
            'clip_duration_seconds' => ['type' => 'number', 'label' => 'Max Clip Duration (seconds)', 'default' => 30],
            'fade_duration_seconds' => ['type' => 'number', 'label' => 'Fade Duration (seconds)', 'default' => 1],
            'output_directory' => ['type' => 'text', 'label' => 'Output Directory', 'default' => 'storage/app/promofy'],
            'resume_from_clip_index' => ['type' => 'number', 'label' => 'Resume From Clip Index', 'default' => 0],
        ];
    }

    public function execute(array $input, WorkflowRunStep $step): array
    {
        $sourceType = (string) ($input['source_type'] ?? '');
        $sourceValue = trim((string) ($input['source_value'] ?? ''));

        if (! in_array($sourceType, ['file_path', 'youtube_video_id', 'youtube_channel_id'], true)) {
            throw new \InvalidArgumentException('PROMOFY source_type must be file_path, youtube_video_id, or youtube_channel_id.');
        }

        if ($sourceValue === '') {
            throw new \InvalidArgumentException('PROMOFY source_value is required.');
        }

        $maxClips = max(1, (int) ($input['max_clips'] ?? 6));
        $maxDuration = max(1, (int) ($input['clip_duration_seconds'] ?? 30));
        $fadeDuration = max(0.1, (float) ($input['fade_duration_seconds'] ?? 1));
        $resumeFrom = max(0, (int) ($input['resume_from_clip_index'] ?? 0));
        $outputDirectory = trim((string) ($input['output_directory'] ?? 'storage/app/promofy'));
        $transcriptEntries = $this->decodeJson($input['transcript_json'] ?? '[]', 'transcript_json');

        $this->logStage('promofy.start', [
            'step_id' => $step->id,
            'source_type' => $sourceType,
            'source_value' => $sourceValue,
            'transcript_entries' => count($transcriptEntries),
        ]);

        $normalized = $this->normalizeTranscriptEntries($transcriptEntries, $sourceType, $sourceValue, $maxDuration);

        $this->logStage('promofy.transcripts.normalized', [
            'step_id' => $step->id,
            'segments' => count($normalized),
        ]);

        $llmPrompt = $this->buildPrompt($sourceType, $sourceValue, $normalized, $maxClips, $maxDuration, (string) ($input['analysis_prompt'] ?? ''));

        $rawLlmResponse = trim((string) ($input['llm_response_json'] ?? ''));
        $selected = $rawLlmResponse !== ''
            ? $this->normalizeLlmClipSelection($this->decodeJson($rawLlmResponse, 'llm_response_json'), $sourceType, $sourceValue, $maxDuration)
            : $this->pickTranscriptMoments($normalized, $maxClips, $maxDuration);

        $selected = array_values(array_slice($selected, $resumeFrom, $maxClips));

        $clips = [];
        $cutCommands = [];
        foreach ($selected as $index => $clip) {
            $clipNumber = $index + 1;
            $start = round((float) $clip['start'], 3);
            $end = round((float) $clip['end'], 3);
            $duration = max(0.1, round($end - $start, 3));
            $videoRef = $clip['video_ref'];
            $videoId = $clip['video_id'];
            $outputPath = rtrim($outputDirectory, '/').'/clip_'.str_pad((string) $clipNumber, 3, '0', STR_PAD_LEFT).'.mp4';

            $clips[] = [
                'index' => $clipNumber,
                'video_id' => $videoId,
                'video_ref' => $videoRef,
                'start' => $start,
                'end' => $end,
                'duration' => $duration,
                'reason' => $clip['reason'] ?? null,
                'output_path' => $outputPath,
            ];

            $cutCommands[] = sprintf(
                'ffmpeg -y -ss %s -i %s -t %s -c copy %s',
                $start,
                escapeshellarg($videoRef),
                $duration,
                escapeshellarg($outputPath)
            );
        }

        $stitchCommand = $this->buildStitchCommand($clips, $outputDirectory, $fadeDuration);

        $this->logStage('promofy.plan.generated', [
            'step_id' => $step->id,
            'clips' => count($clips),
            'resume_from' => $resumeFrom,
        ]);

        return [
            'source' => [
                'type' => $sourceType,
                'value' => $sourceValue,
            ],
            'llm_prompt' => $llmPrompt,
            'clips' => $clips,
            'ffmpeg_cut_commands' => $cutCommands,
            'ffmpeg_stitch_command' => $stitchCommand,
            'checkpoints' => [
                'resume_from_clip_index' => $resumeFrom,
                'next_clip_index' => $resumeFrom + count($clips),
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    protected function decodeJson(mixed $value, string $field): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded)) {
            throw new \InvalidArgumentException("PROMOFY {$field} must be valid JSON.");
        }

        return $decoded;
    }

    protected function normalizeTranscriptEntries(array $entries, string $sourceType, string $sourceValue, int $maxDuration): array
    {
        $normalized = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $start = (float) ($entry['start'] ?? 0);
            $end = isset($entry['end']) ? (float) $entry['end'] : $start + $maxDuration;
            if ($end <= $start) {
                continue;
            }

            $videoId = (string) ($entry['video_id'] ?? $sourceValue);
            $videoRef = match ($sourceType) {
                'file_path' => $sourceValue,
                'youtube_video_id' => 'downloads/'.$sourceValue.'.mp4',
                'youtube_channel_id' => 'downloads/'.$videoId.'.mp4',
                default => $sourceValue,
            };

            $normalized[] = [
                'video_id' => $videoId,
                'video_ref' => $videoRef,
                'start' => $start,
                'end' => min($end, $start + $maxDuration),
                'text' => (string) ($entry['text'] ?? ''),
            ];
        }

        if ($normalized === []) {
            throw new \InvalidArgumentException('PROMOFY transcript_json did not provide any usable transcript segments.');
        }

        return $normalized;
    }

    protected function buildPrompt(string $sourceType, string $sourceValue, array $segments, int $maxClips, int $maxDuration, string $extraPrompt): string
    {
        $excerpt = array_slice($segments, 0, 40);

        return trim(implode("\n", [
            'You are PROMOFY, an expert promo editor.',
            'Analyze transcript segments and pick the best viral-worthy moments.',
            "Source type: {$sourceType}",
            "Source value: {$sourceValue}",
            "Constraints: Return strictly valid JSON with a top-level \"clips\" array, max {$maxClips} clips, max {$maxDuration} seconds each.",
            'Each clip object must include: video_id, start, end, reason.',
            'Transcript segments (JSON): '.json_encode($excerpt, JSON_UNESCAPED_SLASHES),
            $extraPrompt,
        ]));
    }

    protected function normalizeLlmClipSelection(array $selection, string $sourceType, string $sourceValue, int $maxDuration): array
    {
        $clips = $selection['clips'] ?? $selection;
        if (! is_array($clips)) {
            throw new \InvalidArgumentException('PROMOFY llm_response_json must include a clips array.');
        }

        $normalized = [];
        foreach ($clips as $clip) {
            if (! is_array($clip)) {
                continue;
            }

            $start = (float) ($clip['start'] ?? 0);
            $end = isset($clip['end']) ? (float) $clip['end'] : $start + $maxDuration;
            if ($end <= $start) {
                continue;
            }

            $videoId = (string) ($clip['video_id'] ?? $sourceValue);
            $videoRef = match ($sourceType) {
                'file_path' => $sourceValue,
                'youtube_video_id' => 'downloads/'.$sourceValue.'.mp4',
                'youtube_channel_id' => 'downloads/'.$videoId.'.mp4',
                default => $sourceValue,
            };

            $normalized[] = [
                'video_id' => $videoId,
                'video_ref' => $videoRef,
                'start' => $start,
                'end' => min($end, $start + $maxDuration),
                'reason' => (string) ($clip['reason'] ?? ''),
            ];
        }

        if ($normalized === []) {
            throw new \InvalidArgumentException('PROMOFY llm_response_json did not produce usable clips.');
        }

        return $normalized;
    }

    protected function pickTranscriptMoments(array $segments, int $maxClips, int $maxDuration): array
    {
        usort($segments, function (array $a, array $b) {
            return strlen($b['text'] ?? '') <=> strlen($a['text'] ?? '');
        });

        $selected = [];
        foreach ($segments as $segment) {
            if (count($selected) >= $maxClips) {
                break;
            }

            $start = (float) $segment['start'];
            $end = min((float) $segment['end'], $start + $maxDuration);
            if ($end <= $start) {
                continue;
            }

            $selected[] = [
                'video_id' => $segment['video_id'],
                'video_ref' => $segment['video_ref'],
                'start' => $start,
                'end' => $end,
                'reason' => 'High-information transcript segment',
            ];
        }

        return $selected;
    }

    protected function buildStitchCommand(array $clips, string $outputDirectory, float $fadeDuration): ?string
    {
        if (count($clips) === 0) {
            return null;
        }

        if (count($clips) === 1) {
            return 'cp '.escapeshellarg($clips[0]['output_path']).' '.escapeshellarg(rtrim($outputDirectory, '/').'/promofy-final.mp4');
        }

        $inputArgs = [];
        $filterParts = [];

        foreach ($clips as $index => $clip) {
            $inputArgs[] = '-i '.escapeshellarg($clip['output_path']);

            if ($index === 0) {
                continue;
            }

            $left = $index === 1 ? '[0:v]' : '[v'.($index - 1).']';
            $right = '['.$index.':v]';
            $out = '[v'.$index.']';
            $offset = max(0, round(($index * max(0.1, $clip['duration'])) - $fadeDuration, 3));
            $filterParts[] = "{$left}{$right}xfade=transition=fade:duration={$fadeDuration}:offset={$offset}{$out}";
        }

        $lastStream = '[v'.(count($clips) - 1).']';

        return 'ffmpeg -y '.implode(' ', $inputArgs).' -filter_complex '.escapeshellarg(implode(';', $filterParts)).' -map '.escapeshellarg($lastStream).' '.escapeshellarg(rtrim($outputDirectory, '/').'/promofy-final.mp4');
    }

    protected function logStage(string $event, array $context): void
    {
        try {
            if (Container::getInstance()) {
                Log::info($event, $context);
            }
        } catch (\Throwable) {
            // No logger available outside of Laravel runtime.
        }
    }
}
