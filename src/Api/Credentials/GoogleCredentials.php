<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Api\Credentials;

use Jolicode\WalletKit\Exception\Api\GoogleServiceAccountException;

final class GoogleCredentials
{
    /** @var array<string, mixed>|null */
    private ?array $serviceAccountData = null;

    public function __construct(
        public readonly string $serviceAccountJsonPath,
    ) {
    }

    /** @return array<string, mixed> */
    public function getServiceAccountData(): array
    {
        if (null === $this->serviceAccountData) {
            if (!is_readable($this->serviceAccountJsonPath)) {
                throw new GoogleServiceAccountException(\sprintf('Unable to read service account JSON file at "%s".', $this->serviceAccountJsonPath));
            }

            $content = file_get_contents($this->serviceAccountJsonPath);

            if (false === $content) {
                throw new GoogleServiceAccountException(\sprintf('Unable to read service account JSON file at "%s".', $this->serviceAccountJsonPath));
            }

            try {
                /** @var array<string, mixed> $data */
                $data = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new GoogleServiceAccountException(\sprintf('Invalid service account JSON in "%s": %s', $this->serviceAccountJsonPath, $e->getMessage()), 0, $e);
            }

            $this->serviceAccountData = $data;
        }

        return $this->serviceAccountData;
    }
}
