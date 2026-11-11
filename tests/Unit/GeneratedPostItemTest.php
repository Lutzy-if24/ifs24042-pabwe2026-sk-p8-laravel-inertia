<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_from_array_creates_item_with_valid_category(): void
    {
        $item = GeneratedPostItem::fromArray([
            'title'    => 'Test Title',
            'category' => 'Teknologi',
            'excerpt'  => 'Test excerpt.',
            'content'  => 'Test content.',
        ]);

        $this->assertSame('Test Title', $item->title);
        $this->assertSame('Teknologi', $item->category);
        $this->assertSame('Test excerpt.', $item->excerpt);
        $this->assertSame('Test content.', $item->content);
    }

    public function test_from_array_falls_back_to_umum_on_invalid_category(): void
    {
        $item = GeneratedPostItem::fromArray([
            'title'    => 'No Category',
            'category' => 'SomethingInvalid',
            'excerpt'  => '',
            'content'  => 'Content here.',
        ]);
        $this->assertSame(PostCategory::Umum->value, $item->category);
    }

    public function test_from_array_handles_missing_fields(): void
    {
        $item = GeneratedPostItem::fromArray([]);
        $this->assertSame('Artikel Tanpa Judul', $item->title);
        $this->assertSame(PostCategory::Umum->value, $item->category);
        $this->assertSame('', $item->excerpt);
        $this->assertSame('', $item->content);
    }

    public function test_to_array_returns_correct_keys(): void
    {
        $item = GeneratedPostItem::fromArray([
            'title'    => 'T',
            'category' => 'Bisnis',
            'excerpt'  => 'E',
            'content'  => 'C',
        ]);
        $arr = $item->toArray();
        $this->assertArrayHasKey('title', $arr);
        $this->assertArrayHasKey('category', $arr);
        $this->assertArrayHasKey('excerpt', $arr);
        $this->assertArrayHasKey('content', $arr);
    }
}
