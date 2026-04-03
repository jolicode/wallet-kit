<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Android\Model\Shared;

/**
 * @phpstan-type BarcodeRenderEncoding 'RENDER_ENCODING_UNSPECIFIED'|'UTF_8'
 */
enum BarcodeRenderEncodingEnum: string
{
    case UNSPECIFIED = 'RENDER_ENCODING_UNSPECIFIED';
    case UTF_8 = 'UTF_8';
}
