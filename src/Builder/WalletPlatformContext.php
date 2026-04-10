<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Exception\GooglePlatformContextRequiredException;
use Jolicode\WalletKit\Exception\WalletKitInvariantViolationException;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

/**
 * Shared identifiers and defaults for wallet payloads. At least one of {@see $apple}, {@see $google}, or {@see $samsung} must be set before passing to a builder.
 */
final class WalletPlatformContext
{
    public function __construct(
        public readonly ?AppleWalletContext $apple = null,
        public readonly ?GoogleWalletContext $google = null,
        public readonly ?SamsungWalletContext $samsung = null,
    ) {
    }

    public function hasApple(): bool
    {
        return null !== $this->apple;
    }

    public function hasGoogle(): bool
    {
        return null !== $this->google;
    }

    public function hasSamsung(): bool
    {
        return null !== $this->samsung;
    }

    /**
     * Issuer name for Google Wallet *Class* resources. Uses {@see GoogleWalletContext::$issuerName} when set; otherwise Apple {@see AppleWalletContext::$organizationName}.
     *
     * @throws GooglePlatformContextRequiredException when there is no Google context (callers should guard with {@see hasGoogle()})
     * @throws WalletKitInvariantViolationException   if issuer cannot be resolved
     */
    public function googleIssuerName(): string
    {
        if (null === $this->google) {
            throw new GooglePlatformContextRequiredException('googleIssuerName() requires a Google context.');
        }

        if (null !== $this->google->issuerName && '' !== $this->google->issuerName) {
            return $this->google->issuerName;
        }

        if (null !== $this->apple) {
            return $this->apple->organizationName;
        }

        throw new WalletKitInvariantViolationException('Google issuer name is missing and no Apple context is available to fall back on.');
    }

    public function withApple(
        string $teamIdentifier,
        string $passTypeIdentifier,
        string $serialNumber,
        string $organizationName,
        string $description,
        int $formatVersion = 1,
    ): self {
        return new self(
            new AppleWalletContext(
                teamIdentifier: $teamIdentifier,
                passTypeIdentifier: $passTypeIdentifier,
                serialNumber: $serialNumber,
                organizationName: $organizationName,
                description: $description,
                formatVersion: $formatVersion,
            ),
            $this->google,
            $this->samsung,
        );
    }

    public function withGoogle(
        string $classId,
        string $objectId,
        ReviewStatusEnum $defaultReviewStatus = ReviewStatusEnum::DRAFT,
        StateEnum $defaultObjectState = StateEnum::ACTIVE,
        ?string $issuerName = null,
    ): self {
        return new self(
            $this->apple,
            new GoogleWalletContext(
                classId: $classId,
                objectId: $objectId,
                defaultReviewStatus: $defaultReviewStatus,
                defaultObjectState: $defaultObjectState,
                issuerName: $issuerName,
            ),
            $this->samsung,
        );
    }

    public function withSamsung(
        string $refId,
        string $language = 'en',
        ?string $appLinkLogo = null,
        ?string $appLinkName = null,
        ?string $appLinkData = null,
    ): self {
        return new self(
            $this->apple,
            $this->google,
            new SamsungWalletContext(
                refId: $refId,
                language: $language,
                appLinkLogo: $appLinkLogo,
                appLinkName: $appLinkName,
                appLinkData: $appLinkData,
            ),
        );
    }
}
