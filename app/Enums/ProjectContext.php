<?php

namespace App\Enums;

/**
 * Which of the two sites a project is presented on.
 *
 * The company site (sheikhnabil.com) shows client work delivered by the studio;
 * the founder's About page shows his own portfolio. A project belongs to exactly
 * one, so the two never blur together on either page.
 */
enum ProjectContext: string
{
    case Company = 'company';
    case Personal = 'personal';

    public function label(): string
    {
        return match ($this) {
            self::Company => 'Company work',
            self::Personal => 'Personal portfolio',
        };
    }

    /**
     * Every case as `{ value, label }`, for the admin's project form select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $context): array => ['value' => $context->value, 'label' => $context->label()],
            self::cases(),
        );
    }
}
