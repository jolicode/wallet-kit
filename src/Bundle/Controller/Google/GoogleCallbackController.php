<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Controller\Google;

use Jolicode\WalletKit\Bundle\Google\GoogleCallbackHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GoogleCallbackController
{
    public function __construct(
        private readonly ?GoogleCallbackHandlerInterface $handler = null,
    ) {
    }

    public function handleCallback(Request $request): Response
    {
        /** @var array<string, mixed> $body */
        $body = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (null === $this->handler) {
            return new Response('', Response::HTTP_OK);
        }

        if (\array_key_exists('eventType', $body)) {
            $eventType = (string) $body['eventType'];
            $classId = '';
            $objectId = '';

            if (\array_key_exists('classId', $body)) {
                $classId = (string) $body['classId'];
            }
            if (\array_key_exists('objectId', $body)) {
                $objectId = (string) $body['objectId'];
            }

            match ($eventType) {
                'save' => $this->handler->onPassSaved($classId, $objectId),
                'del' => $this->handler->onPassDeleted($classId, $objectId),
                default => null,
            };
        }

        return new Response('', Response::HTTP_OK);
    }
}
