<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests;

use Jolicode\WalletKit\Common\Color;
use Jolicode\WalletKit\Exception\InvalidColorException;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function testFromRgb(): void
    {
        $color = Color::fromRgb(22, 55, 110);

        self::assertSame('rgb(22, 55, 110)', $color->rgb());
        self::assertSame('#16376e', $color->hex());
    }

    public function testFromHex(): void
    {
        $color = Color::fromHex('#16376e');

        self::assertSame('rgb(22, 55, 110)', $color->rgb());
        self::assertSame('#16376e', $color->hex());
    }

    public function testFromHexUppercase(): void
    {
        $color = Color::fromHex('#FF6B35');

        self::assertSame('rgb(255, 107, 53)', $color->rgb());
        self::assertSame('#ff6b35', $color->hex());
    }

    public function testFromHexAllHexDigitsUppercase(): void
    {
        $color = Color::fromHex('#ABCDEF');

        self::assertSame('rgb(171, 205, 239)', $color->rgb());
        self::assertSame('#abcdef', $color->hex());
    }

    public function testFromRgbString(): void
    {
        $color = Color::fromRgbString('rgb(10, 20, 30)');

        self::assertSame('rgb(10, 20, 30)', $color->rgb());
        self::assertSame('#0a141e', $color->hex());
    }

    public function testRoundTripRgbToHex(): void
    {
        $original = Color::fromRgb(255, 128, 0);
        $roundTripped = Color::fromHex($original->hex());

        self::assertSame($original->rgb(), $roundTripped->rgb());
        self::assertSame($original->hex(), $roundTripped->hex());
    }

    public function testRoundTripHexToRgb(): void
    {
        $original = Color::fromHex('#4a2f1b');
        $roundTripped = Color::fromRgbString($original->rgb());

        self::assertSame($original->rgb(), $roundTripped->rgb());
        self::assertSame($original->hex(), $roundTripped->hex());
    }

    public function testFromRgbInvalidRange(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::fromRgb(256, 0, 0);
    }

    public function testFromRgbNegative(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::fromRgb(-1, 0, 0);
    }

    public function testFromHexInvalidFormat(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::fromHex('not-a-color');
    }

    public function testFromHexMissingHash(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::fromHex('ff6b35');
    }

    public function testFromRgbStringInvalidFormat(): void
    {
        $this->expectException(InvalidColorException::class);
        Color::fromRgbString('not-rgb');
    }

    public function testBlack(): void
    {
        $color = Color::fromRgb(0, 0, 0);

        self::assertSame('rgb(0, 0, 0)', $color->rgb());
        self::assertSame('#000000', $color->hex());
    }

    public function testWhite(): void
    {
        $color = Color::fromRgb(255, 255, 255);

        self::assertSame('rgb(255, 255, 255)', $color->rgb());
        self::assertSame('#ffffff', $color->hex());
    }
}
