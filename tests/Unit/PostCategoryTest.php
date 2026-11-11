<?php

namespace Tests\Unit;

use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\TestCase;

class PostCategoryTest extends TestCase
{
    public function test_all_cases_have_correct_values(): void
    {
        $this->assertSame('Teknologi', PostCategory::Teknologi->value);
        $this->assertSame('Pendidikan', PostCategory::Pendidikan->value);
        $this->assertSame('Gaya Hidup', PostCategory::GayaHidup->value);
        $this->assertSame('Bisnis', PostCategory::Bisnis->value);
        $this->assertSame('Opini', PostCategory::Opini->value);
        $this->assertSame('Umum', PostCategory::Umum->value);
    }

    public function test_label_returns_long_name(): void
    {
        $this->assertSame('Teknologi & Koding', PostCategory::Teknologi->label());
        $this->assertSame('Pendidikan & Tutorial', PostCategory::Pendidikan->label());
        $this->assertSame('Gaya Hidup & Produktivitas', PostCategory::GayaHidup->label());
        $this->assertSame('Bisnis & Karir', PostCategory::Bisnis->label());
        $this->assertSame('Opini & Pemikiran', PostCategory::Opini->label());
        $this->assertSame('Umum', PostCategory::Umum->label());
    }

    public function test_values_returns_array_of_strings(): void
    {
        $values = PostCategory::values();
        $this->assertIsArray($values);
        $this->assertContains('Teknologi', $values);
        $this->assertContains('Umum', $values);
        $this->assertCount(6, $values);
    }

    public function test_from_value_or_default_returns_correct_case(): void
    {
        $this->assertSame(PostCategory::Teknologi, PostCategory::fromValueOrDefault('Teknologi'));
        $this->assertSame(PostCategory::Bisnis, PostCategory::fromValueOrDefault('Bisnis'));
    }

    public function test_from_value_or_default_falls_back_to_umum(): void
    {
        $this->assertSame(PostCategory::Umum, PostCategory::fromValueOrDefault('InvalidCategory'));
        $this->assertSame(PostCategory::Umum, PostCategory::fromValueOrDefault(''));
    }

    public function test_options_returns_value_label_pairs(): void
    {
        $options = PostCategory::options();
        $this->assertIsArray($options);
        $this->assertCount(6, $options);
        $this->assertArrayHasKey('value', $options[0]);
        $this->assertArrayHasKey('label', $options[0]);
    }
}
