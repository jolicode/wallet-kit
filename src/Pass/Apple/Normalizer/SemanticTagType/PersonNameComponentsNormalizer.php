<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Apple\Normalizer\SemanticTagType;

use Jolicode\WalletKit\Pass\Apple\Model\SemanticTagType\PersonNameComponents;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type PersonNameComponentsType from PersonNameComponents
 */
class PersonNameComponentsNormalizer implements NormalizerInterface
{
    /**
     * @param PersonNameComponents $object
     * @param array<string, mixed> $context
     *
     * @return PersonNameComponentsType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        if (null !== $object->familyName) {
            $data['familyName'] = $object->familyName;
        }

        if (null !== $object->givenName) {
            $data['givenName'] = $object->givenName;
        }

        if (null !== $object->middleName) {
            $data['middleName'] = $object->middleName;
        }

        if (null !== $object->namePrefix) {
            $data['namePrefix'] = $object->namePrefix;
        }

        if (null !== $object->nameSuffix) {
            $data['nameSuffix'] = $object->nameSuffix;
        }

        if (null !== $object->nickname) {
            $data['nickname'] = $object->nickname;
        }

        if (null !== $object->phoneticRepresentation) {
            $data['phoneticRepresentation'] = $object->phoneticRepresentation;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PersonNameComponents;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [PersonNameComponents::class => true];
    }
}
