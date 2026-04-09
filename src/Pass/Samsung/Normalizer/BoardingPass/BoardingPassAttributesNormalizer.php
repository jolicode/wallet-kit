<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Pass\Samsung\Normalizer\BoardingPass;

use Jolicode\WalletKit\Pass\Samsung\Model\BoardingPass\BoardingPassAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-import-type BoardingPassAttributesType from BoardingPassAttributes
 */
class BoardingPassAttributesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param BoardingPassAttributes $object
     * @param array<string, mixed>   $context
     *
     * @return BoardingPassAttributesType
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [
            'title' => $object->title,
            'providerName' => $object->providerName,
            'bgColor' => $object->bgColor->hex(),
            'appLinkLogo' => $object->appLinkLogo,
            'appLinkName' => $object->appLinkName,
            'appLinkData' => $object->appLinkData,
        ];

        if (null !== $object->providerLogo) {
            $data['providerLogo'] = $this->normalizer->normalize($object->providerLogo, $format, $context);
        }

        if (null !== $object->user) {
            $data['user'] = $object->user;
        }

        if (null !== $object->vehicleNumber) {
            $data['vehicleNumber'] = $object->vehicleNumber;
        }

        if (null !== $object->seatClass) {
            $data['seatClass'] = $object->seatClass;
        }

        if (null !== $object->seatNumber) {
            $data['seatNumber'] = $object->seatNumber;
        }

        if (null !== $object->reservationNumber) {
            $data['reservationNumber'] = $object->reservationNumber;
        }

        if (null !== $object->departName) {
            $data['departName'] = $object->departName;
        }

        if (null !== $object->departCode) {
            $data['departCode'] = $object->departCode;
        }

        if (null !== $object->departTerminal) {
            $data['departTerminal'] = $object->departTerminal;
        }

        if (null !== $object->arriveName) {
            $data['arriveName'] = $object->arriveName;
        }

        if (null !== $object->arriveCode) {
            $data['arriveCode'] = $object->arriveCode;
        }

        if (null !== $object->arriveTerminal) {
            $data['arriveTerminal'] = $object->arriveTerminal;
        }

        if (null !== $object->estimatedOrActualStartDate) {
            $data['estimatedOrActualStartDate'] = $object->estimatedOrActualStartDate;
        }

        if (null !== $object->estimatedOrActualEndDate) {
            $data['estimatedOrActualEndDate'] = $object->estimatedOrActualEndDate;
        }

        if (null !== $object->boardingTime) {
            $data['boardingTime'] = $object->boardingTime;
        }

        if (null !== $object->gateClosingTime) {
            $data['gateClosingTime'] = $object->gateClosingTime;
        }

        if (null !== $object->gate) {
            $data['gate'] = $object->gate;
        }

        if (null !== $object->boardingGroup) {
            $data['boardingGroup'] = $object->boardingGroup;
        }

        if (null !== $object->boardingSeqNo) {
            $data['boardingSeqNo'] = $object->boardingSeqNo;
        }

        if (null !== $object->baggageAllowance) {
            $data['baggageAllowance'] = $object->baggageAllowance;
        }

        if (null !== $object->barcode) {
            $data['barcode'] = $this->normalizer->normalize($object->barcode, $format, $context);
        }

        if (null !== $object->noticeDesc) {
            $data['noticeDesc'] = $object->noticeDesc;
        }

        if (null !== $object->preventCapture) {
            $data['preventCaptureYn'] = $object->preventCapture ? 'Y' : 'N';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof BoardingPassAttributes;
    }

    /** @return array<class-string, bool> */
    public function getSupportedTypes(?string $format): array
    {
        return [BoardingPassAttributes::class => true];
    }
}
