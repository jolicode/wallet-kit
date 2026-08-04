<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Builder;

use Jolicode\WalletKit\Builder\WalletSerializerFactory;
use Symfony\Component\Serializer\Serializer;

final class BuilderTestSerializerFactory
{
    public static function create(): Serializer
    {
        return WalletSerializerFactory::create();
    }
}
