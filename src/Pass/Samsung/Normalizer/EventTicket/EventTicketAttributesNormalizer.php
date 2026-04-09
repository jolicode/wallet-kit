<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\EventTicket;

use Jolicode\WalletKit\Pass\Samsung\Model\EventTicket\EventTicketAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type EventTicketAttributesType from EventTicketAttributes
 */
class EventTicketAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param EventTicketAttributes $object
     * @param array<string, mixed>  $context
     *
     * @return EventTicketAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'providerName' => $object->providerName,
            'issueDate' => $object->issueDate,
            'reservationNumber' => $object->reservationNumber,
            'startDate' => $object->startDate,
            'noticeDesc' => $object->noticeDesc,
            'appLinkLogo' => $object->appLinkLogo,
            'appLinkName' => $object->appLinkName,
            'appLinkData' => $object->appLinkData,
        ];

        if (null !== $object->mainImg) {
            $data['mainImg'] = $object->mainImg;
        }

        if (null !== $object->logoImage) {
            $data['logoImage'] = $this->normalizer->normalize($object->logoImage, $format, $context);
        }

        if (null !== $object->endDate) {
            $data['endDate'] = $object->endDate;
        }

        if (null !== $object->holderName) {
            $data['holderName'] = $object->holderName;
        }

        if (null !== $object->grade) {
            $data['grade'] = $object->grade;
        }

        if (null !== $object->seatNumber) {
            $data['seatNumber'] = $object->seatNumber;
        }

        if (null !== $object->entrance) {
            $data['entrance'] = $object->entrance;
        }

        if (null !== $object->barcode) {
            $data['barcode'] = $this->normalizer->normalize($object->barcode, $format, $context);
        }

        if (null !== $object->bgColor) {
            $data['bgColor'] = $object->bgColor->hex();
        }

        if (null !== $object->fontColor) {
            $data['fontColor'] = $object->fontColor->hex();
        }

        if (null !== $object->locations) {
            $locations = [];
            foreach ($object->locations as $location) {
                $locations[] = $this->normalizer->normalize($location, $format, $context);
            }
            $data['locations'] = $locations;
        }

        if (null !== $object->preventCapture) {
            $data['preventCaptureYn'] = $object->preventCapture ? 'Y' : 'N';
        }

        if (null !== $object->noNetworkSupport) {
            $data['noNetworkSupportYn'] = $object->noNetworkSupport ? 'Y' : 'N';
        }

        if (null !== $object->reactivatable) {
            $data['reactivatableYn'] = $object->reactivatable ? 'Y' : 'N';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EventTicketAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [EventTicketAttributes::class => true];
    }
}
