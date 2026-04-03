<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Builder;

use Jolicode\WalletKit\Exception\GooglePlatformContextRequiredException;
use Jolicode\WalletKit\Exception\InvalidWalletPlatformContextException;
use Jolicode\WalletKit\Exception\WalletKitInvariantViolationException;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ReviewStatusEnum;
use Jolicode\WalletKit\Pass\Android\Model\Shared\StateEnum;

/**
 * Shared identifiers and defaults for wallet payloads. At least one of {@see $apple} or {@see $google} must be set.
 */
final class WalletPlatformContext
{
    public function __construct(
        public readonly ?AppleWalletContext $apple = null,
        public readonly ?GoogleWalletContext $google = null,
    ) {
        if (null === $this->apple && null === $this->google) {
            throw InvalidWalletPlatformContextException::missingPlatformSlice();
        }

        if (null !== $this->google && null === $this->apple) {
            $issuer = $this->google->issuerName;
            if (null === $issuer || '' === $issuer) {
                throw InvalidWalletPlatformContextException::googleIssuerNameRequiredWhenAppleAbsent();
            }
        }
    }

    public function hasApple(): bool
    {
        return null !== $this->apple;
    }

    public function hasGoogle(): bool
    {
        return null !== $this->google;
    }

    /**
     * Issuer name for Google Wallet *Class* resources. Uses {@see GoogleWalletContext::$issuerName} when set; otherwise Apple {@see AppleWalletContext::$organizationName}.
     *
     * @throws GooglePlatformContextRequiredException when there is no Google context (callers should guard with {@see hasGoogle()})
     * @throws WalletKitInvariantViolationException   if issuer cannot be resolved despite constructor rules
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

        throw new WalletKitInvariantViolationException('Google issuer name is missing; this should have been rejected in the constructor.');
    }

    /**
     * Same parameters as the historical single constructor: dual-platform context with issuer mirrored from Apple organization name.
     */
    public static function both(
        string $appleTeamIdentifier,
        string $applePassTypeIdentifier,
        string $appleSerialNumber,
        string $appleOrganizationName,
        string $appleDescription,
        string $googleClassId,
        string $googleObjectId,
        int $appleFormatVersion = 1,
        ReviewStatusEnum $defaultGoogleReviewStatus = ReviewStatusEnum::DRAFT,
        StateEnum $defaultGoogleObjectState = StateEnum::ACTIVE,
    ): self {
        $apple = new AppleWalletContext(
            teamIdentifier: $appleTeamIdentifier,
            passTypeIdentifier: $applePassTypeIdentifier,
            serialNumber: $appleSerialNumber,
            organizationName: $appleOrganizationName,
            description: $appleDescription,
            formatVersion: $appleFormatVersion,
        );

        $google = new GoogleWalletContext(
            classId: $googleClassId,
            objectId: $googleObjectId,
            defaultReviewStatus: $defaultGoogleReviewStatus,
            defaultObjectState: $defaultGoogleObjectState,
            issuerName: $appleOrganizationName,
        );

        return new self($apple, $google);
    }

    public static function appleOnly(
        string $appleTeamIdentifier,
        string $applePassTypeIdentifier,
        string $appleSerialNumber,
        string $appleOrganizationName,
        string $appleDescription,
        int $appleFormatVersion = 1,
    ): self {
        return new self(new AppleWalletContext(
            teamIdentifier: $appleTeamIdentifier,
            passTypeIdentifier: $applePassTypeIdentifier,
            serialNumber: $appleSerialNumber,
            organizationName: $appleOrganizationName,
            description: $appleDescription,
            formatVersion: $appleFormatVersion,
        ), null);
    }

    public static function googleOnly(
        string $googleClassId,
        string $googleObjectId,
        string $issuerName,
        ReviewStatusEnum $defaultGoogleReviewStatus = ReviewStatusEnum::DRAFT,
        StateEnum $defaultGoogleObjectState = StateEnum::ACTIVE,
    ): self {
        return new self(null, new GoogleWalletContext(
            classId: $googleClassId,
            objectId: $googleObjectId,
            defaultReviewStatus: $defaultGoogleReviewStatus,
            defaultObjectState: $defaultGoogleObjectState,
            issuerName: $issuerName,
        ));
    }
}
