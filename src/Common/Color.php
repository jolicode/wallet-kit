<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Common;

use Jolicode\WalletKit\Exception\InvalidColorException;

final readonly class Color
{
    private function __construct(
        private int $r,
        private int $g,
        private int $b,
    ) {
    }

    public static function fromRgb(int $r, int $g, int $b): self
    {
        if ($r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b > 255) {
            throw new InvalidColorException(\sprintf('RGB values must be between 0 and 255, got (%d, %d, %d).', $r, $g, $b));
        }

        return new self($r, $g, $b);
    }

    public static function fromHex(string $hex): self
    {
        if (preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $hex, $m) !== 1) {
            throw new InvalidColorException(\sprintf('Invalid hex color "%s", expected format #rrggbb.', $hex));
        }

        return new self((int) hexdec($m[1]), (int) hexdec($m[2]), (int) hexdec($m[3]));
    }

    public static function fromRgbString(string $rgb): self
    {
        if (preg_match('/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i', $rgb, $m) !== 1) {
            throw new InvalidColorException(\sprintf('Invalid RGB string "%s", expected format rgb(r, g, b).', $rgb));
        }

        return self::fromRgb((int) $m[1], (int) $m[2], (int) $m[3]);
    }

    public function rgb(): string
    {
        return \sprintf('rgb(%d, %d, %d)', $this->r, $this->g, $this->b);
    }

    public function hex(): string
    {
        return \sprintf('#%02x%02x%02x', $this->r, $this->g, $this->b);
    }
}
