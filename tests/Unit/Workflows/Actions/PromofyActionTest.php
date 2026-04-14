<?php

namespace Tests\Unit\Workflows\Actions;

use App\Models\WorkflowRunStep;
use App\Workflows\Actions\PromofyAction;
use PHPUnit\Framework\TestCase;

class PromofyActionTest extends TestCase
{
    public function test_it_generates_clips_from_transcript_when_llm_response_is_missing(): void
    {
        $action = new PromofyAction;
        $step = new WorkflowRunStep;

        $result = $action->execute([
            'source_type' => 'file_path',
            'source_value' => '/videos/source.mp4',
            'transcript_json' => json_encode([
                ['start' => 0, 'end' => 12, 'text' => 'Short moment'],
                ['start' => 15, 'end' => 45, 'text' => 'This is a much longer high-energy moment that should be prioritized'],
            ]),
            'max_clips' => 1,
            'clip_duration_seconds' => 20,
            'output_directory' => 'storage/app/promofy',
        ], $step);

        $this->assertCount(1, $result['clips']);
        $this->assertStringContainsString('ffmpeg -y -i', $result['ffmpeg_cut_commands'][0]);
        $this->assertStringContainsString('-c:v libx264 -c:a aac', $result['ffmpeg_cut_commands'][0]);
        $this->assertSame('/videos/source.mp4', $result['clips'][0]['video_ref']);
        $this->assertSame(0, $result['checkpoints']['resume_from_clip_index']);
    }

    public function test_it_uses_llm_json_and_resume_clip_index(): void
    {
        $action = new PromofyAction;
        $step = new WorkflowRunStep;

        $result = $action->execute([
            'source_type' => 'youtube_channel_id',
            'source_value' => 'UC123',
            'transcript_json' => json_encode([
                ['video_id' => 'abc', 'start' => 1, 'end' => 20, 'text' => 'first'],
                ['video_id' => 'xyz', 'start' => 30, 'end' => 55, 'text' => 'second'],
            ]),
            'llm_response_json' => json_encode([
                'clips' => [
                    ['video_id' => 'abc', 'start' => 1, 'end' => 15, 'reason' => 'hook'],
                    ['video_id' => 'xyz', 'start' => 31, 'end' => 45, 'reason' => 'payoff'],
                ],
            ]),
            'resume_from_clip_index' => 0,
        ], $step);

        $this->assertCount(2, $result['clips']);
        $this->assertSame('abc', $result['clips'][0]['video_id']);
        $this->assertSame(2, $result['checkpoints']['next_clip_index']);
        $this->assertIsString($result['ffmpeg_stitch_command']);
        $this->assertStringContainsString('[1:v:0]', $result['ffmpeg_stitch_command']);
        $this->assertStringContainsString('offset=13', $result['ffmpeg_stitch_command']);
    }
}
