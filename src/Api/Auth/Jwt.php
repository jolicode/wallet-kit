<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Auth;

final class Jwt
{
    public static function base64UrlEncode(string $data): string
    {
        return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        return \base64_decode(\strtr($data, '-_', '+/'), true) ?: '';
    }
}
