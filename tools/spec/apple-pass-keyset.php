#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Usage:
 *   php apple-pass-keyset.php generate   — print JSON keyset from src/Pass/Apple/Model phpstan shapes
 *   php apple-pass-keyset.php check      — compare generate() to tools/spec/apple-pass-keyset.json (exit 1 if diff)
 *   php apple-pass-keyset.php baseline   — overwrite apple-pass-keyset.json with generate() output
 */

$cmd = $argv[1] ?? '';

if (!\in_array($cmd, ['generate', 'check', 'baseline'], true)) {
    fwrite(STDERR, "Usage: php apple-pass-keyset.php generate|check|baseline\n");
    exit(2);
}

$modelDir = dirname(__DIR__, 2) . '/src/Pass/Apple/Model';
$keysetPath = __DIR__ . '/apple-pass-keyset.json';

/** @var array<string, array{0: string, 1: string}> */
$entities = [
    'pass' => ['Pass.php', 'PassType'],
    'passStructure' => ['PassStructure.php', 'PassStructureType'],
    'field' => ['Field.php', 'FieldType'],
    'semanticTags' => ['SemanticTags.php', 'SemanticTagsType'],
    'barcode' => ['Barcode.php', 'BarcodeType'],
    'nfc' => ['Nfc.php', 'NfcType'],
    'location' => ['Location.php', 'LocationType'],
    'beacon' => ['Beacon.php', 'BeaconType'],
    'relevantDate' => ['RelevantDate.php', 'RelevantDateType'],
];

$generated = buildKeyset($modelDir, $entities);
$encoded = encodeKeyset($generated);

if ($cmd === 'generate') {
    echo $encoded;
    exit(0);
}

if ($cmd === 'baseline') {
    if (file_put_contents($keysetPath, $encoded) === false) {
        fwrite(STDERR, "Could not write {$keysetPath}\n");
        exit(2);
    }
    echo "Wrote {$keysetPath}\n";
    exit(0);
}

// check
if (!is_file($keysetPath)) {
    fwrite(STDERR, "Missing keyset file: {$keysetPath}\nRun: castor spec:baseline:apple\n");
    exit(2);
}

$baselineRaw = file_get_contents($keysetPath);
if ($baselineRaw === false) {
    fwrite(STDERR, "Could not read {$keysetPath}\n");
    exit(2);
}

try {
    $baselineDecoded = json_decode($baselineRaw, true, 512, JSON_THROW_ON_ERROR);
} catch (\JsonException) {
    fwrite(STDERR, "Invalid JSON in baseline.\n");
    exit(2);
}

$normBaseline = normalizeKeyset($baselineDecoded);
$normGenerated = normalizeKeyset($generated);

if ($normBaseline === $normGenerated) {
    echo "OK: Apple pass keyset matches baseline.\n";
    exit(0);
}

echo "Apple pass keyset mismatch (entities => sorted JSON keys from phpstan array shapes).\n";
reportDiff($normBaseline, $normGenerated);
exit(1);

/**
 * @param array<string, array{0: string, 1: string}> $entities
 *
 * @return array{entities: array<string, list<string>>}
 */
function buildKeyset(string $modelDir, array $entities): array
{
    $out = ['entities' => []];
    foreach ($entities as $name => [$file, $typeName]) {
        $path = $modelDir . '/' . $file;
        if (!is_file($path)) {
            throw new RuntimeException("Missing model file: {$path}");
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Could not read: {$path}");
        }
        $keys = extractKeysFromPhpStanArrayShape($content, $typeName);
        $out['entities'][$name] = $keys;
    }

    ksort($out['entities']);

    return $out;
}

/**
 * @return list<string>
 */
function extractKeysFromPhpStanArrayShape(string $content, string $typeName): array
{
    $pattern = '/@phpstan-type\s+' . preg_quote($typeName, '/') . '\s+array\s*\{/';
    if (!preg_match($pattern, $content, $m, PREG_OFFSET_CAPTURE)) {
        throw new RuntimeException("Could not find @phpstan-type {$typeName} array{ in file.");
    }

    $openBracePos = $m[0][1] + strlen($m[0][0]) - 1;
    $body = extractBalancedBraceBody($content, $openBracePos);
    $keys = [];

    foreach (preg_split('/\R/', $body) as $rawLine) {
        $line = trim($rawLine);
        $line = preg_replace('/^\*\s*/', '', $line);
        if ($line === '' || str_starts_with($line, '//')) {
            continue;
        }
        // One line may contain several properties (e.g. BarcodeType on a single line).
        if (preg_match_all('/(?:^|,\s*)([a-zA-Z_][a-zA-Z0-9_]*)\??\s*:/', $line, $km)) {
            foreach ($km[1] as $key) {
                $keys[] = $key;
            }
        }
    }

    $keys = array_values(array_unique($keys));
    sort($keys);

    return $keys;
}

function extractBalancedBraceBody(string $content, int $openBracePos): string
{
    $len = strlen($content);
    if ($openBracePos >= $len || $content[$openBracePos] !== '{') {
        throw new RuntimeException('Expected { at array shape start.');
    }

    $depth = 0;
    $bodyStart = $openBracePos + 1;

    for ($i = $openBracePos; $i < $len; $i++) {
        $c = $content[$i];
        if ($c === '{') {
            $depth++;
        } elseif ($c === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($content, $bodyStart, $i - $bodyStart);
            }
        }
    }

    throw new RuntimeException('Unclosed array shape braces.');
}

/**
 * @param array{entities?: array<string, mixed>} $data
 *
 * @return array{entities: array<string, list<string>>}
 */
function normalizeKeyset(array $data): array
{
    $entities = $data['entities'] ?? [];
    if (!\is_array($entities)) {
        return ['entities' => []];
    }

    $out = ['entities' => []];
    foreach ($entities as $name => $keys) {
        if (!\is_array($keys)) {
            continue;
        }
        $list = [];
        foreach ($keys as $k) {
            if (\is_string($k)) {
                $list[] = $k;
            }
        }
        sort($list);
        $out['entities'][(string) $name] = $list;
    }
    ksort($out['entities']);

    return $out;
}

/**
 * @param array{entities: array<string, list<string>>} $baseline
 * @param array{entities: array<string, list<string>>} $generated
 */
function reportDiff(array $baseline, array $generated): void
{
    $allEntities = array_unique([...array_keys($baseline['entities']), ...array_keys($generated['entities'])]);
    sort($allEntities);

    foreach ($allEntities as $entity) {
        $b = $baseline['entities'][$entity] ?? [];
        $g = $generated['entities'][$entity] ?? [];
        if ($b === $g) {
            continue;
        }
        echo "\n[{$entity}]\n";
        $onlyB = array_values(array_diff($b, $g));
        $onlyG = array_values(array_diff($g, $b));
        if ($onlyB !== []) {
            echo '  Only in baseline JSON: ' . implode(', ', $onlyB) . "\n";
        }
        if ($onlyG !== []) {
            echo '  Only in generated (code): ' . implode(', ', $onlyG) . "\n";
        }
    }
}

/**
 * @param array{entities: array<string, list<string>>} $keyset
 */
function encodeKeyset(array $keyset): string
{
    return json_encode($keyset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
}
