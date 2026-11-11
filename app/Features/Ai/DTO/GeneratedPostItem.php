<?php

namespace App\Features\Ai\DTO;

use App\Features\Post\Enums\PostCategory;

class GeneratedPostItem
{
    public function __construct(
        public readonly string $title,
        public readonly string $category,
        public readonly string $excerpt,
        public readonly string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        $category = PostCategory::fromValueOrDefault($data['category'] ?? '')->value;

        return new self(
            title:    trim($data['title']    ?? 'Artikel Tanpa Judul'),
            category: $category,
            excerpt:  trim($data['excerpt']  ?? ''),
            content:  trim($data['content']  ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'title'    => $this->title,
            'category' => $this->category,
            'excerpt'  => $this->excerpt,
            'content'  => $this->content,
        ];
    }
}
