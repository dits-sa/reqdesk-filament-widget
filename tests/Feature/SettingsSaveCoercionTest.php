<?php

declare(strict_types=1);

use Reqdesk\Filament\Enums\ThemeMode;
use Reqdesk\Filament\Filament\Pages\ReqdeskSettings;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;

/**
 * Locks down the type-coercion contract on ReqdeskSettings::save().
 *
 * Filament + Livewire + JSON round-tripping hand back values whose
 * runtime type does not always match the spatie-settings property
 * declaration:
 *
 *   - Select(options: BackedEnum::class) → BackedEnum case, not string.
 *   - TextInput(numeric/integer) → float (Livewire JSON-decodes "9999"
 *     as a number, which in PHP becomes a float).
 *   - Toggle in some Filament versions → "1" / "0" string instead of bool.
 *   - ?string fields with empty input → "" instead of null.
 *
 * coerceForProperty has to make every one of those land cleanly under
 * strict_types. Tests here exercise each case once.
 */
function coerce(string $key, mixed $value): mixed
{
    return ReqdeskSettings::coerceForProperty(ReqdeskWidgetSettings::class, $key, $value);
}

it('coerces a float to int for theme_z_index', function (): void {
    expect(coerce('theme_z_index', 9999.0))->toBe(9999)
        ->and(coerce('theme_z_index', 9999.7))->toBe(10000)
        ->and(coerce('theme_z_index', '12345'))->toBe(12345);
});

it('falls back to the property default when an int field receives an empty value', function (): void {
    expect(coerce('theme_z_index', null))->toBe(9999)
        ->and(coerce('theme_z_index', ''))->toBe(9999);
});

it('coerces a BackedEnum to its scalar value for string properties', function (): void {
    expect(coerce('theme_mode', ThemeMode::Auto))->toBe('auto');
});

it('coerces an empty string to null for nullable string properties', function (): void {
    expect(coerce('api_key', ''))->toBeNull()
        ->and(coerce('api_url', null))->toBeNull();
});

it('coerces "1" / "0" / "true" / "false" strings to bools for bool properties', function (): void {
    expect(coerce('theme_hide_branding', '1'))->toBeTrue()
        ->and(coerce('theme_hide_branding', '0'))->toBeFalse()
        ->and(coerce('theme_hide_branding', 'true'))->toBeTrue()
        ->and(coerce('theme_hide_branding', 'false'))->toBeFalse()
        ->and(coerce('theme_hide_branding', 1))->toBeTrue()
        ->and(coerce('theme_hide_branding', 0))->toBeFalse();
});

it('passes arrays through unchanged', function (): void {
    expect(coerce('panels', ['admin', 'agent']))->toBe(['admin', 'agent']);
});

it('coerces a non-array to an empty array for array properties', function (): void {
    expect(coerce('panels', null))->toBe([])
        ->and(coerce('panels', 'not-an-array'))->toBe([]);
});

it('preserves numeric strings for string properties', function (): void {
    expect(coerce('theme_border_radius', '8px'))->toBe('8px')
        ->and(coerce('theme_primary_color', '#abcdef'))->toBe('#abcdef');
});

it('coerces ints and floats to strings for string properties', function (): void {
    expect(coerce('theme_border_radius', 8))->toBe('8')
        ->and(coerce('theme_border_radius', 8.5))->toBe('8.5');
});

it('returns null for nullable string fields when given an array', function (): void {
    expect(coerce('api_key', ['foo']))->toBeNull();
});

it('handles unknown property gracefully (passes through after enum unwrap)', function (): void {
    expect(coerce('this_property_does_not_exist', 'x'))->toBe('x')
        ->and(coerce('this_property_does_not_exist', ThemeMode::Auto))->toBe('auto');
});
