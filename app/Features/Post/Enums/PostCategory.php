<?php

namespace App\Features\Post\Enums;

enum PostCategory: string
{
    case Teknologi    = 'Teknologi';
    case Pendidikan   = 'Pendidikan';
    case GayaHidup    = 'Gaya Hidup';
    case Bisnis       = 'Bisnis';
    case Opini        = 'Opini';
    case Umum         = 'Umum';

    public function label(): string
    {
        return match ($this) {
            self::Teknologi  => 'Teknologi & Koding',
            self::Pendidikan => 'Pendidikan & Tutorial',
            self::GayaHidup  => 'Gaya Hidup & Produktivitas',
            self::Bisnis     => 'Bisnis & Karir',
            self::Opini      => 'Opini & Pemikiran',
            self::Umum       => 'Umum',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValueOrDefault(string $value): self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return self::Umum;
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
