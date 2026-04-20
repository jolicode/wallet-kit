<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle;

use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Api\Credentials\GoogleCredentials;
use Jolicode\WalletKit\Api\Credentials\SamsungCredentials;
use Jolicode\WalletKit\Builder\WalletPlatformContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class WalletContextFactory
{
    /**
     * @param string $routePrefix Reserved for future route-prefix customization
     */
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ?AppleCredentials $appleCredentials,
        private readonly ?GoogleCredentials $googleCredentials = null,
        private readonly ?SamsungCredentials $samsungCredentials = null,
        private readonly string $routePrefix = '/wallet-kit',
    ) {
    }

    public function getGoogleCredentials(): ?GoogleCredentials
    {
        return $this->googleCredentials;
    }

    public function getSamsungCredentials(): ?SamsungCredentials
    {
        return $this->samsungCredentials;
    }

    public function getRoutePrefix(): string
    {
        return $this->routePrefix;
    }

    public function createContext(): WalletPlatformContext
    {
        $context = new WalletPlatformContext();

        if (null !== $this->appleCredentials
            && null !== $this->appleCredentials->teamIdentifier
            && null !== $this->appleCredentials->passTypeIdentifier
        ) {
            $webServiceURL = $this->urlGenerator->generate(
                'wallet_kit_apple_register_device',
                [
                    'deviceId' => '__DEVICE_ID__',
                    'passTypeId' => $this->appleCredentials->passTypeIdentifier,
                    'serialNumber' => '__SERIAL_NUMBER__',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            // Strip the placeholder path segments to get the base web service URL
            $webServiceURL = str_replace(
                '/apple/v1/devices/__DEVICE_ID__/registrations/' . $this->appleCredentials->passTypeIdentifier . '/__SERIAL_NUMBER__',
                '',
                $webServiceURL,
            );

            $context = $context->withAppleDefaults(
                teamIdentifier: $this->appleCredentials->teamIdentifier,
                passTypeIdentifier: $this->appleCredentials->passTypeIdentifier,
                webServiceURL: $webServiceURL,
            );
        }

        return $context;
    }
}
