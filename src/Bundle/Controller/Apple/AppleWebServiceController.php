<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Controller\Apple;

use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Bundle\Apple\ApplePassProviderInterface;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AppleWebServiceController
{
    public function __construct(
        private readonly PassRegistrationRepositoryInterface $registrationRepository,
        private readonly ApplePassProviderInterface $passProvider,
        private readonly ApplePassPackager $passPackager,
    ) {
    }

    public function registerDevice(Request $request, string $deviceId, string $passTypeId, string $serialNumber): Response
    {
        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $pushToken = '';
        if (\array_key_exists('pushToken', $body)) {
            $pushToken = (string) $body['pushToken'];
        }

        $this->registrationRepository->register($deviceId, $passTypeId, $serialNumber, $pushToken);

        return new Response('', Response::HTTP_CREATED);
    }

    public function unregisterDevice(Request $request, string $deviceId, string $passTypeId, string $serialNumber): Response
    {
        $this->registrationRepository->unregister($deviceId, $passTypeId, $serialNumber);

        return new Response('', Response::HTTP_OK);
    }

    public function getSerialNumbers(Request $request, string $deviceId, string $passTypeId): JsonResponse
    {
        $passesUpdatedSince = $request->query->get('passesUpdatedSince');
        $serialNumbers = [];

        if (\is_string($passesUpdatedSince) && '' !== $passesUpdatedSince) {
            $since = new \DateTimeImmutable($passesUpdatedSince);
            $serialNumbers = $this->passProvider->getUpdatedSerialNumbers($passTypeId, $since);
        } else {
            $serialNumbers = $this->registrationRepository->findSerialNumbers($deviceId, $passTypeId);
        }

        if (0 === \count($serialNumbers)) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse([
            'serialNumbers' => $serialNumbers,
            'lastUpdated' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]);
    }

    public function getLatestPass(string $passTypeId, string $serialNumber): Response
    {
        $builtPass = $this->passProvider->getPass($passTypeId, $serialNumber);
        $images = $this->passProvider->getPassImages($passTypeId, $serialNumber);

        $pkpassContent = $this->passPackager->package($builtPass->apple(), $images);

        return new Response($pkpassContent, Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.apple.pkpass',
        ]);
    }

    public function log(Request $request): Response
    {
        return new Response('', Response::HTTP_OK);
    }
}
