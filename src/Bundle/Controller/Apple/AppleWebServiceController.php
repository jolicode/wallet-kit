<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Controller\Apple;

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Bundle\Apple\ApplePassProviderInterface;
use Jolicode\WalletKit\Bundle\Apple\AppleRegistrationHandlerInterface;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationOutcome;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AppleWebServiceController
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly PassRegistrationRepositoryInterface $registrationRepository,
        private readonly ApplePassProviderInterface $passProvider,
        private readonly ApplePassPackager $passPackager,
        private readonly ?AppleRegistrationHandlerInterface $registrationHandler = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function registerDevice(Request $request, string $deviceId, string $passTypeId, string $serialNumber): Response
    {
        if (!$this->authenticatePassRequest($request, $passTypeId, $serialNumber)) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        /* @var array<string, mixed> $body */
        try {
            $body = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // Malformed payloads must not surface as 500 — Apple (or anything else) may send garbage.
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $pushToken = '';
        if (\array_key_exists('pushToken', $body)) {
            $pushToken = (string) $body['pushToken'];
        }

        $result = $this->registrationRepository->register($deviceId, $passTypeId, $serialNumber, $pushToken);

        if (PassRegistrationOutcome::Created === $result->outcome) {
            $this->notifyRegistrationHandler(
                'onDeviceRegistered',
                fn (AppleRegistrationHandlerInterface $registrationHandler) => $registrationHandler->onDeviceRegistered($deviceId, $passTypeId, $serialNumber, $pushToken),
            );
        } elseif (PassRegistrationOutcome::TokenUpdated === $result->outcome) {
            $this->logPushTokenRotation($deviceId, $passTypeId, $serialNumber, $pushToken);

            $this->notifyRegistrationHandler(
                'onPushTokenRotated',
                fn (AppleRegistrationHandlerInterface $registrationHandler) => $registrationHandler->onPushTokenRotated(
                    $deviceId,
                    $passTypeId,
                    $serialNumber,
                    (string) $result->previousPushToken,
                    $pushToken,
                ),
            );
        }
        // Unchanged: idempotent no-op — nothing logged, no hook called.

        return new Response('', PassRegistrationOutcome::Created === $result->outcome ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    public function unregisterDevice(Request $request, string $deviceId, string $passTypeId, string $serialNumber): Response
    {
        if (!$this->authenticatePassRequest($request, $passTypeId, $serialNumber)) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $removed = $this->registrationRepository->unregister($deviceId, $passTypeId, $serialNumber);

        if (false === $removed) {
            // Apple expects a 2xx on unregister even when registration data does not
            // match the stored row (device mismatch, pruned rows...): a failure answer
            // leaves zombie registrations that keep receiving pushes. Replying 2xx
            // indiscriminately is safe — the authentication token already proved the
            // caller owns this pass.
            $this->logger->warning('Apple Wallet pass unregistration without matching device', [
                'deviceId' => $deviceId,
                'passTypeId' => $passTypeId,
                'serialNumber' => $serialNumber,
            ]);

            return new Response('', Response::HTTP_OK);
        }

        $this->notifyRegistrationHandler(
            'onDeviceUnregistered',
            fn (AppleRegistrationHandlerInterface $registrationHandler) => $registrationHandler->onDeviceUnregistered($deviceId, $passTypeId, $serialNumber),
        );

        return new Response('', Response::HTTP_OK);
    }

    public function getSerialNumbers(Request $request, string $deviceId, string $passTypeId): Response
    {
        if (!$this->authenticateDeviceRequest($request, $deviceId, $passTypeId)) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $passesUpdatedSince = $request->query->get('passesUpdatedSince');
        $serialNumbers = [];

        if (\is_string($passesUpdatedSince) && '' !== $passesUpdatedSince) {
            try {
                $since = new \DateTimeImmutable($passesUpdatedSince);
            } catch (\Exception) {
                return new Response('', Response::HTTP_BAD_REQUEST);
            }

            $serialNumbers = $this->passProvider->getUpdatedSerialNumbers($passTypeId, $since);
        } else {
            $serialNumbers = $this->registrationRepository->findSerialNumbers($deviceId, $passTypeId);
        }

        if (0 === \count($serialNumbers)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $lastUpdated = $this->computeLastUpdated($passTypeId, $serialNumbers);

        return new JsonResponse([
            'serialNumbers' => $serialNumbers,
            'lastUpdated' => $lastUpdated->format(\DateTimeInterface::ATOM),
        ]);
    }

    public function getLatestPass(Request $request, string $passTypeId, string $serialNumber): Response
    {
        if (!$this->authenticatePassRequest($request, $passTypeId, $serialNumber)) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $lastModified = $this->passProvider->getLastModified($passTypeId, $serialNumber);

        $ifModifiedSinceHeader = $request->headers->get('If-Modified-Since');
        if (null !== $lastModified && null !== $ifModifiedSinceHeader) {
            try {
                $ifModifiedSince = new \DateTimeImmutable($ifModifiedSinceHeader);
                if ($lastModified <= $ifModifiedSince) {
                    return new Response('', Response::HTTP_NOT_MODIFIED);
                }
            } catch (\Exception) {
                // Invalid header — fall through and return the pass.
            }
        }

        $builtPass = $this->passProvider->getPass($passTypeId, $serialNumber);
        $images = $this->passProvider->getPassImages($passTypeId, $serialNumber);

        $pkpassContent = $this->passPackager->package($builtPass->apple(), $images);

        $headers = ['Content-Type' => 'application/vnd.apple.pkpass'];
        if (null !== $lastModified) {
            $headers['Last-Modified'] = $lastModified->format(\DateTimeInterface::RFC7231);
        }

        return new Response($pkpassContent, Response::HTTP_OK, $headers);
    }

    public function log(Request $request): Response
    {
        $content = $request->getContent();

        if ('' !== $content) {
            try {
                $body = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
                if (\is_array($body) && \array_key_exists('logs', $body) && \is_array($body['logs'])) {
                    foreach ($body['logs'] as $entry) {
                        $this->logger->debug('Apple PassKit device log: {entry}', ['entry' => $entry]);
                    }
                }
            } catch (\JsonException) {
                // Ignore malformed log payloads — Apple spec does not require us to act on them.
            }
        }

        return new Response('', Response::HTTP_OK);
    }

    private function logPushTokenRotation(string $deviceId, string $passTypeId, string $serialNumber, string $newPushToken): void
    {
        $this->logger->info('Apple Wallet pass push token rotated', [
            'deviceId' => $deviceId,
            'passTypeId' => $passTypeId,
            'serialNumber' => $serialNumber,
            // Hashed: the raw push token must not end up in logs.
            'pushTokenHash' => substr(hash('sha256', $newPushToken), 0, 12),
        ]);
    }

    /**
     * @param \Closure(AppleRegistrationHandlerInterface): void $call
     */
    private function notifyRegistrationHandler(string $lifecycleStep, \Closure $call): void
    {
        if (null === $this->registrationHandler) {
            return;
        }

        try {
            $call($this->registrationHandler);
        } catch (\Throwable $exception) {
            // Best-effort: an application handler failure must never break the
            // Apple Web Service contract with the device.
            $this->logger->warning(\sprintf('Apple Wallet registration handler %s failed: %s', $lifecycleStep, $exception->getMessage()), [
                'exception' => $exception::class,
            ]);
        }
    }

    private function authenticatePassRequest(Request $request, string $passTypeId, string $serialNumber): bool
    {
        $expected = $this->passProvider->getAuthenticationToken($passTypeId, $serialNumber);

        if (null === $expected) {
            return false;
        }

        $token = $this->extractApplePassToken($request);

        return null !== $token && hash_equals($expected, $token);
    }

    /**
     * The serial-numbers endpoint has no serial in the URL: Apple authenticates
     * with the token of any pass the device is registered to for this pass type.
     */
    private function authenticateDeviceRequest(Request $request, string $deviceId, string $passTypeId): bool
    {
        $token = $this->extractApplePassToken($request);

        if (null === $token) {
            return false;
        }

        foreach ($this->registrationRepository->findSerialNumbers($deviceId, $passTypeId) as $serialNumber) {
            $expected = $this->passProvider->getAuthenticationToken($passTypeId, $serialNumber);

            if (null !== $expected && hash_equals($expected, $token)) {
                return true;
            }
        }

        return false;
    }

    private function extractApplePassToken(Request $request): ?string
    {
        $header = $request->headers->get('Authorization') ?? '';

        if (1 !== preg_match('/^ApplePass\s+(.+)$/', $header, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    /**
     * @param string[] $serialNumbers
     */
    private function computeLastUpdated(string $passTypeId, array $serialNumbers): \DateTimeImmutable
    {
        $latest = null;
        foreach ($serialNumbers as $serialNumber) {
            $modified = $this->passProvider->getLastModified($passTypeId, $serialNumber);
            if (null !== $modified && (null === $latest || $modified > $latest)) {
                $latest = $modified;
            }
        }

        return $latest ?? new \DateTimeImmutable();
    }
}
