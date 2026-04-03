<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder\Internal;

final class ColorMapper
{
    /**
     * Converts `rgb(r, g, b)` to `#rrggbb` for Google hex fields. Returns null if the pattern does not match.
     */
    public static function appleRgbToGoogleHex(?string $appleBackground): ?string
    {
        if (null === $appleBackground) {
            return null;
        }

        if (preg_match('/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)\s*$/i', $appleBackground, $m) !== 1) {
            return null;
        }

        $r = max(0, min(255, (int) $m[1]));
        $g = max(0, min(255, (int) $m[2]));
        $b = max(0, min(255, (int) $m[3]));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
