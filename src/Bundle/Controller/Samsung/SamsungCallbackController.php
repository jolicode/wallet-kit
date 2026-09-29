<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Bundle\Controller\Samsung;

use Jolicode\WalletKit\Bundle\Samsung\SamsungNotificationVerifier;
use Jolicode\WalletKit\Bundle\Samsung\SamsungCallbackHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SamsungCallbackController
{
    public function __construct(
        private readonly ?SamsungCallbackHandlerInterface $handler = null,
        private readonly ?SamsungNotificationVerifier $verifier = null,
    ) {
    }

    public function handleCallback(Request $request): Response
    {
        if (null !== $this->verifier && !$this->verifier->verify($request)) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        /** @var array<string, mixed> $body */
        try {
            $body = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if (null === $this->handler) {
            return new Response('', Response::HTTP_OK);
        }

        $cardId = '';
        $newState = '';

        if (\array_key_exists('cardId', $body)) {
            $cardId = (string) $body['cardId'];
        }
        if (\array_key_exists('state', $body)) {
            $newState = (string) $body['state'];
        }

        if ('' !== $cardId && '' !== $newState) {
            $this->handler->onCardStateChanged($cardId, $newState);
        }

        return new Response('', Response::HTTP_OK);
    }
}
