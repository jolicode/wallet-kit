#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Usage: php google-wallet-spec.php check|baseline
 * Compares or updates tools/spec/google-wallet-baseline.json against live Wallet Objects discovery.
 */

const DISCOVERY_URL = 'https://walletobjects.googleapis.com/$discovery/rest?version=v1';

$root = dirname(__DIR__, 2);
$baselinePath = __DIR__ . '/google-wallet-baseline.json';

$cmd = $argv[1] ?? '';

if (!\in_array($cmd, ['check', 'baseline'], true)) {
    fwrite(STDERR, "Usage: php google-wallet-spec.php check|baseline\n");
    exit(2);
}

$discoveryJson = fetchDiscoveryJson();
if ($discoveryJson === null) {
    fwrite(STDERR, "Failed to fetch discovery document.\n");
    exit(2);
}

$live = json_decode($discoveryJson, true);
if (!\is_array($live)) {
    fwrite(STDERR, "Invalid discovery JSON.\n");
    exit(2);
}

$liveRevision = $live['revision'] ?? null;
$liveVersion = $live['version'] ?? null;

if ($liveRevision === null || $liveRevision === '') {
    fwrite(STDERR, "Discovery response missing revision.\n");
    exit(2);
}

if ($cmd === 'baseline') {
    $payload = [
        'revision' => $liveRevision,
        'version' => $liveVersion,
        'updatedAt' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM),
    ];
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
    if (file_put_contents($baselinePath, $encoded) === false) {
        fwrite(STDERR, "Could not write {$baselinePath}\n");
        exit(2);
    }
    echo "Wrote baseline: revision {$liveRevision}, version " . ($liveVersion ?? 'null') . "\n";
    exit(0);
}

// check
if (!is_file($baselinePath)) {
    fwrite(STDERR, "Missing baseline file: {$baselinePath}\nRun: castor spec:baseline:google\n");
    exit(2);
}

$baselineRaw = file_get_contents($baselinePath);
if ($baselineRaw === false) {
    fwrite(STDERR, "Could not read {$baselinePath}\n");
    exit(2);
}

try {
    $baseline = json_decode($baselineRaw, true, 512, JSON_THROW_ON_ERROR);
} catch (\JsonException) {
    fwrite(STDERR, "Invalid JSON in baseline.\n");
    exit(2);
}

$expectedRevision = $baseline['revision'] ?? null;
$expectedVersion = $baseline['version'] ?? null;

if ($expectedRevision !== $liveRevision) {
    echo "Google Wallet discovery revision mismatch.\n";
    echo "  Live:     revision={$liveRevision}, version=" . ($liveVersion ?? 'null') . "\n";
    echo "  Baseline: revision={$expectedRevision}, version=" . ($expectedVersion ?? 'null') . "\n";
    echo "After updating the library for the new API surface, run: castor spec:baseline:google\n";
    exit(1);
}

echo "OK: discovery revision {$liveRevision} matches baseline.\n";
exit(0);

function fetchDiscoveryJson(): ?string
{
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 30,
            'header' => "Accept: application/json\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $result = @file_get_contents(DISCOVERY_URL, false, $ctx);

    return \is_string($result) && $result !== '' ? $result : null;
}
