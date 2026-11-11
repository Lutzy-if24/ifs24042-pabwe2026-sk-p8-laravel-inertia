<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;

interface AiClientInterface
{
    /**
     * Generate a list of blog post drafts.
     *
     * @param  int  $count  Number of posts to generate (1–10)
     * @return GeneratedPostItem[]
     */
    public function generatePosts(int $count): array;
}
