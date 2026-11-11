<?php

namespace Tests\Feature;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->member()->create();
    }

    public function test_ai_generate_returns_posts_using_mock(): void
    {
        $mockItems = [
            new GeneratedPostItem('AI Title 1', PostCategory::Teknologi->value, 'Excerpt 1', 'Content 1'),
            new GeneratedPostItem('AI Title 2', PostCategory::Bisnis->value, 'Excerpt 2', 'Content 2'),
        ];

        $this->app->bind(AiClientInterface::class, function () use ($mockItems) {
            return new class($mockItems) implements AiClientInterface {
                public function __construct(private array $items) {}
                public function generatePosts(int $count): array { return $this->items; }
            };
        });

        $response = $this->actingAs($this->member)
            ->postJson(route('dashboard.posts.ai-generate'), ['count' => 2]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(2, 'posts');
    }

    public function test_ai_generate_validates_count(): void
    {
        $response = $this->actingAs($this->member)
            ->postJson(route('dashboard.posts.ai-generate'), ['count' => 0]);
        $response->assertStatus(422);

        $response = $this->actingAs($this->member)
            ->postJson(route('dashboard.posts.ai-generate'), ['count' => 11]);
        $response->assertStatus(422);
    }

    public function test_ai_generate_handles_client_error(): void
    {
        $this->app->bind(AiClientInterface::class, function () {
            return new class implements AiClientInterface {
                public function generatePosts(int $count): array {
                    throw new \RuntimeException('API Error');
                }
            };
        });

        $response = $this->actingAs($this->member)
            ->postJson(route('dashboard.posts.ai-generate'), ['count' => 1]);
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_ai_save_creates_draft_posts(): void
    {
        $response = $this->actingAs($this->member)->post(route('dashboard.posts.ai-save'), [
            'posts' => [
                [
                    'title'    => 'AI Draft 1',
                    'category' => PostCategory::Teknologi->value,
                    'excerpt'  => 'Excerpt 1',
                    'content'  => 'Full content 1',
                ],
                [
                    'title'    => 'AI Draft 2',
                    'category' => PostCategory::Opini->value,
                    'excerpt'  => '',
                    'content'  => 'Full content 2',
                ],
            ],
        ]);

        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertDatabaseHas('posts', ['title' => 'AI Draft 1', 'is_published' => false]);
        $this->assertDatabaseHas('posts', ['title' => 'AI Draft 2', 'is_published' => false]);
    }

    public function test_ai_save_validates_posts_array(): void
    {
        $response = $this->actingAs($this->member)->post(route('dashboard.posts.ai-save'), [
            'posts' => [],
        ]);
        $response->assertSessionHasErrors(['posts']);
    }

    public function test_openai_service_uses_http_facade(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                [
                                    'title'    => 'HTTP Test Title',
                                    'category' => 'Teknologi',
                                    'excerpt'  => 'Test excerpt',
                                    'content'  => 'Test content',
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new \App\Features\Ai\Services\OpenAiPostGeneratorService();
        $result  = $service->generatePosts(1);

        $this->assertCount(1, $result);
        $this->assertSame('HTTP Test Title', $result[0]->title);
        Http::assertSent(fn ($req) => str_contains($req->url(), 'chat/completions'));
    }

    public function test_openai_service_handles_api_failure(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response('Server Error', 500),
        ]);

        $service = new \App\Features\Ai\Services\OpenAiPostGeneratorService();

        $this->expectException(\RuntimeException::class);
        $service->generatePosts(1);
    }

    public function test_openai_service_handles_invalid_json(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'not json at all']]],
            ], 200),
        ]);

        $service = new \App\Features\Ai\Services\OpenAiPostGeneratorService();
        $this->expectException(\RuntimeException::class);
        $service->generatePosts(1);
    }

    public function test_openai_service_strips_code_fence(): void
    {
        $jsonContent = json_encode([
            ['title' => 'Fence Title', 'category' => 'Umum', 'excerpt' => 'E', 'content' => 'C'],
        ]);

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => "```json\n{$jsonContent}\n```"]]],
            ], 200),
        ]);

        $service = new \App\Features\Ai\Services\OpenAiPostGeneratorService();
        $result  = $service->generatePosts(1);
        $this->assertCount(1, $result);
        $this->assertSame('Fence Title', $result[0]->title);
    }

    public function test_guest_cannot_access_ai_endpoints(): void
    {
        $this->post(route('dashboard.posts.ai-generate'), [])->assertRedirect(route('login'));
        $this->post(route('dashboard.posts.ai-save'), [])->assertRedirect(route('login'));
    }
}
