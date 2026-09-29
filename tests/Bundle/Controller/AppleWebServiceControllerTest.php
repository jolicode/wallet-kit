<?php

declare(strict_types=1);

namespace Jolicode\WalletKit\Tests\Bundle\Controller\Apple;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Jolicode\WalletKit\Api\Apple\ApplePassPackager;
use Jolicode\WalletKit\Api\Credentials\AppleCredentials;
use Jolicode\WalletKit\Bundle\Apple\ApplePassProviderInterface;
use Jolicode\WalletKit\Bundle\Controller\Apple\AppleWebServiceController;
use Jolicode\WalletKit\Bundle\Repository\DoctrinePassRegistrationRepository;
use Jolicode\WalletKit\Bundle\Repository\PassRegistrationRepositoryInterface;
use Jolicode\WalletKit\Tests\Builder\BuilderTestSerializerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class AppleWebServiceControllerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PassRegistrationRepositoryInterface $repository;
    private AppleWebServiceController $controller;
    private string $passTypeId = 'pass.com.example';

    protected function setUp(): void
    {
        if (!\in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('The pdo_sqlite extension is not available.');
        }

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $config = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/../../../../src/Bundle/Entity']);

        if (\PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

        $this->entityManager = new EntityManager($connection, $config);

        $connection->executeStatement('CREATE TABLE wallet_kit_pass_registration (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            deviceId STRING NOT NULL,
            passTypeId STRING NOT NULL,
            serialNumber STRING NOT NULL,
            pushToken STRING NOT NULL,
            registeredAt STRING NOT NULL
        )');

        $this->repository = new DoctrinePassRegistrationRepository($this->entityManager);

        $certFile = $this->createCertificates();
        $credentials = new AppleCredentials(
            certificatePath: $certFile['p12'],
            certificatePassword: 'test',
            wwdrCertificatePath: $certFile['wwdr'],
            teamIdentifier: 'TEAM123',
            passTypeIdentifier: $this->passTypeId,
        );
        $packager = new ApplePassPackager(BuilderTestSerializerFactory::create(), $credentials);

        $this->controller = new AppleWebServiceController(
            $this->repository,
            new StubApplePassProvider(),
            $packager,
        );
    }

    private function createCertificates(): array
    {
        $dir = sys_get_temp_dir() . '/apple-ws-' . bin2hex(random_bytes(4));
        mkdir($dir);

        $caKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        $caCsr = openssl_csr_new(['CN' => 'Test WWDR CA'], $caKey);
        $caCert = openssl_csr_sign($caCsr, null, $caKey, 365);
        openssl_x509_export($caCert, $caPem);
        file_put_contents("$dir/wwdr.pem", $caPem);

        $leafKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        $leafCsr = openssl_csr_new(['CN' => 'Pass Type ID: ' . $this->passTypeId], $leafKey);
        $leafCert = openssl_csr_sign($leafCsr, $caCert, $caKey, 365);

        $p12File = "$dir/cert.p12";
        openssl_pkcs12_export_to_file($leafCert, $p12File, $leafKey, 'test');

        return ['p12' => $p12File, 'wwdr' => "$dir/wwdr.pem"];
    }

    private function request(string $method, string $uri, array $headers = [], ?string $content = null): Request
    {
        $request = Request::create($uri, $method, [], [], [], $headers, $content);
        $request->attributes->set('deviceId', 'device-1');
        $request->attributes->set('passTypeId', $this->passTypeId);
        $request->attributes->set('serialNumber', 'SERIAL-001');

        return $request;
    }

    private function authHeader(string $token = 'token-A'): array
    {
        return ['HTTP_AUTHORIZATION' => 'ApplePass ' . $token];
    }

    public function testRegisterDeviceWithoutAuthReturns401(): void
    {
        $response = $this->controller->registerDevice(
            $this->request('POST', '/x', content: '{"pushToken": "pt-1"}'),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testRegisterDeviceWithWrongAuthReturns401(): void
    {
        $response = $this->controller->registerDevice(
            $this->request('POST', '/x', $this->authHeader('nope'), '{"pushToken": "pt-1"}'),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testRegisterDeviceCreatesThenUpdatesRegistration(): void
    {
        // First registration → 201 created
        $first = $this->controller->registerDevice(
            $this->request('POST', '/x', $this->authHeader(), '{"pushToken": "pt-one"}'),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );
        self::assertSame(201, $first->getStatusCode());

        // iOS re-register (token rotation) → 200 already-registered
        $second = $this->controller->registerDevice(
            $this->request('POST', '/x', $this->authHeader(), '{"pushToken": "pt-rotated"}'),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );
        self::assertSame(200, $second->getStatusCode());

        // The rotated token must have replaced the stale one.
        self::assertSame(['pt-rotated'], $this->repository->findPushTokens(
            $this->passTypeId,
            'SERIAL-001',
        ));
    }

    public function testRegisterDeviceRejectsMalformedJsonWith400(): void
    {
        $response = $this->controller->registerDevice(
            $this->request('POST', '/x', $this->authHeader(), '{not json'),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );

        self::assertSame(400, $response->getStatusCode());
    }

    public function testGetSerialNumbersRequiresAuth(): void
    {
        $response = $this->controller->getSerialNumbers(
            $this->request('GET', '/x'),
            'device-1',
            $this->passTypeId,
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetSerialNumbersAcceptsAnyRegisteredPassTokenForDevice(): void
    {
        // The device has no registration yet — but the pass provider says the
        // token belongs to a registered pass when the device is registered.
        $this->repository->register('device-1', $this->passTypeId, 'SERIAL-001', 'pt-1');

        $response = $this->controller->getSerialNumbers(
            $this->request('GET', '/x', $this->authHeader()),
            'device-1',
            $this->passTypeId,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['SERIAL-001'], json_decode($response->getContent() ?: '', true)['serialNumbers']);
    }

    public function testGetSerialNumbersRejectsTokenOfOtherDevice(): void
    {
        // Device-2 registered; device-1 asks with device-2's pass token.
        $this->repository->register('device-2', $this->passTypeId, 'SERIAL-002', 'pt-2');

        $request = $this->request('GET', '/x', $this->authHeader('token-B'));
        $response = $this->controller->getSerialNumbers($request, 'device-1', $this->passTypeId);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetSerialNumbersRejectsInvalidPassesUpdatedSince(): void
    {
        $this->repository->register('device-1', $this->passTypeId, 'SERIAL-001', 'pt-1');

        $request = $this->request('GET', '/x', $this->authHeader());
        $request->query->set('passesUpdatedSince', 'not-a-date');

        $response = $this->controller->getSerialNumbers($request, 'device-1', $this->passTypeId);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testUnregisterDeviceReturns204AndRemovesRow(): void
    {
        $this->repository->register('device-1', $this->passTypeId, 'SERIAL-001', 'pt-1');

        $response = $this->controller->unregisterDevice(
            $this->request('DELETE', '/x', $this->authHeader()),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertSame([], json_decode($response->getContent() ?: '[]', true) ?? [], 'no body');
    }

    public function testUnregisterDeviceRequiresAuth(): void
    {
        $response = $this->controller->unregisterDevice(
            $this->request('DELETE', '/x'),
            'device-1',
            $this->passTypeId,
            'SERIAL-001',
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetLatestPassRequiresAuth(): void
    {
        $response = $this->controller->getLatestPass(
            $this->request('GET', '/x'),
            $this->passTypeId,
            'SERIAL-001',
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testLogAlwaysReturns200(): void
    {
        $response = $this->controller->log(
            $this->request('POST', '/x', content: '{garbage'),
        );

        self::assertSame(200, $response->getStatusCode());
    }
}

/**
 * Serial → token mapping used by the auth tests; tokens are unique per serial,
 * which is exactly how Apple's auth pairing works in the web service protocol.
 */
final class StubApplePassProvider implements ApplePassProviderInterface
{
    private const TOKENS = [
        'pass.com.example|SERIAL-001' => 'token-A',
        'pass.com.example|SERIAL-002' => 'token-B',
    ];

    public function getPass(string $passTypeIdentifier, string $serialNumber): \Jolicode\WalletKit\Builder\BuiltWalletPass
    {
        throw new \RuntimeException('not used in these tests');
    }

    public function getPassImages(string $passTypeIdentifier, string $serialNumber): array
    {
        return [];
    }

    public function getUpdatedSerialNumbers(string $passTypeIdentifier, \DateTimeInterface $since): array
    {
        return [];
    }

    public function getAuthenticationToken(string $passTypeIdentifier, string $serialNumber): ?string
    {
        return self::TOKENS[$passTypeIdentifier . '|' . $serialNumber] ?? null;
    }

    public function getLastModified(string $passTypeIdentifier, string $serialNumber): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-01-01 00:00:00');
    }
}
