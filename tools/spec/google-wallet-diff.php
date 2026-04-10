#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compares the live Google Wallet discovery document against the PHP models
 * in src/Pass/Android/Model/ to detect:
 *   - missing UPPER_CASE enum values in existing PHP enums
 *   - camelCase aliases present in the API (informational)
 *   - discovery enums with no matching PHP enum
 *   - new properties on modeled schemas (opt-in with --properties)
 *
 * Usage:
 *   php google-wallet-diff.php              — enum diff only
 *   php google-wallet-diff.php --properties — include schema property comparison
 *
 * Exit codes:
 *   0 — no actionable differences
 *   1 — actionable differences found
 *   2 — runtime error
 */

const DISCOVERY_URL = 'https://walletobjects.googleapis.com/$discovery/rest?version=v1';

$root = dirname(__DIR__, 2);
$modelDir = $root . '/src/Pass/Android/Model';
$baselinePath = __DIR__ . '/google-wallet-baseline.json';
$showProperties = \in_array('--properties', $argv, true);

// ── 1. Fetch live discovery ──────────────────────────────────────────────────

$discoveryJson = fetchDiscoveryJson();
if ($discoveryJson === null) {
    fwrite(STDERR, "Failed to fetch discovery document.\n");
    exit(2);
}

$discovery = json_decode($discoveryJson, true);
if (!\is_array($discovery)) {
    fwrite(STDERR, "Invalid discovery JSON.\n");
    exit(2);
}

$liveRevision = $discovery['revision'] ?? '?';
$baselineRevision = '?';
if (is_file($baselinePath)) {
    $bl = json_decode((string) file_get_contents($baselinePath), true);
    $baselineRevision = $bl['revision'] ?? '?';
}

echo "Baseline revision: {$baselineRevision}\n";
echo "Live revision:     {$liveRevision}\n";
if ($baselineRevision === $liveRevision) {
    echo "Revisions match — comparing models anyway.\n";
}
echo str_repeat('─', 72) . "\n\n";

// ── 2. Extract discovery enums ───────────────────────────────────────────────

/** @var array<string, list<string>> $discoveryEnums  schema.property => values */
$discoveryEnums = [];
$schemas = $discovery['schemas'] ?? [];
foreach ($schemas as $schemaName => $schema) {
    if (!isset($schema['properties']) || !\is_array($schema['properties'])) {
        continue;
    }
    foreach ($schema['properties'] as $propName => $prop) {
        if (isset($prop['enum']) && \is_array($prop['enum'])) {
            $discoveryEnums["{$schemaName}.{$propName}"] = $prop['enum'];
        }
    }
}

// ── 3. Scan PHP enums ────────────────────────────────────────────────────────

/** @var array<string, array{file: string, values: list<string>}> $phpEnums */
$phpEnums = scanPhpEnums($modelDir);

// ── 4. Match discovery enums to PHP enums ────────────────────────────────────
// Strategy: for each discovery enum, find the PHP enum whose UPPER_CASE
// values have the best overlap.

/** @var array<string, string> $matchedDiscoveryToPhp */
$matchedDiscoveryToPhp = [];

/** @var array<string, list<string>> $matchedPhpToDiscovery */
$matchedPhpToDiscovery = [];

foreach ($discoveryEnums as $discoveryPath => $discoveryValues) {
    $upperValues = array_filter($discoveryValues, fn (string $v): bool => isUpperCase($v));

    $bestMatch = null;
    $bestOverlap = 0;
    foreach ($phpEnums as $phpClass => $phpData) {
        $overlap = \count(array_intersect($phpData['values'], $upperValues));
        if ($overlap > $bestOverlap) {
            $bestOverlap = $overlap;
            $bestMatch = $phpClass;
        }
    }

    $minOverlap = min(2, \count($phpEnums[$bestMatch]['values'] ?? []));
    if ($bestMatch !== null && $bestOverlap >= $minOverlap) {
        $matchedDiscoveryToPhp[$discoveryPath] = $bestMatch;
        $matchedPhpToDiscovery[$bestMatch][] = $discoveryPath;
    }
}

// ── 5. Report enum differences ───────────────────────────────────────────────

$hasActionable = false;
$actionableEnums = [];
$infoEnums = [];

foreach ($matchedPhpToDiscovery as $phpClass => $discoveryPaths) {
    $phpValues = $phpEnums[$phpClass]['values'];

    $allDiscoveryValues = [];
    foreach ($discoveryPaths as $dp) {
        $allDiscoveryValues = array_merge($allDiscoveryValues, $discoveryEnums[$dp]);
    }
    $allDiscoveryValues = array_values(array_unique($allDiscoveryValues));

    $upperDiscovery = array_values(array_unique(array_filter($allDiscoveryValues, fn (string $v): bool => isUpperCase($v))));
    $camelDiscovery = array_values(array_filter($allDiscoveryValues, fn (string $v): bool => !isUpperCase($v)));

    $missingUpper = array_values(array_diff($upperDiscovery, $phpValues));
    $extraInPhp = array_values(array_diff($phpValues, $upperDiscovery));

    sort($missingUpper);
    sort($camelDiscovery);
    sort($extraInPhp);

    if ($missingUpper === [] && $extraInPhp === []) {
        continue;
    }

    $relFile = str_replace($root . '/', '', $phpEnums[$phpClass]['file']);

    if ($missingUpper !== []) {
        $hasActionable = true;
        $actionableEnums[] = [
            'class' => $phpClass,
            'file' => $relFile,
            'discovery' => $discoveryPaths,
            'missingUpper' => $missingUpper,
            'camel' => $camelDiscovery,
            'extra' => $extraInPhp,
        ];
    } elseif ($extraInPhp !== []) {
        $infoEnums[] = [
            'class' => $phpClass,
            'file' => $relFile,
            'discovery' => $discoveryPaths,
            'extra' => $extraInPhp,
        ];
    }
}

// Print actionable enums first
if ($actionableEnums !== []) {
    echo "ENUMS WITH MISSING VALUES\n\n";
    foreach ($actionableEnums as $e) {
        echo "  {$e['class']} ({$e['file']})\n";
        echo "    Discovery: " . implode(', ', $e['discovery']) . "\n";
        echo "    Missing:   " . implode(', ', $e['missingUpper']) . "\n";
        if ($e['extra'] !== []) {
            echo "    Extra in PHP: " . implode(', ', $e['extra']) . "\n";
        }
        echo "\n";
    }
}

// Print enums with extra values in PHP
if ($infoEnums !== []) {
    echo "ENUMS WITH EXTRA PHP VALUES (not in discovery)\n\n";
    foreach ($infoEnums as $e) {
        echo "  {$e['class']} ({$e['file']})\n";
        echo "    Extra in PHP: " . implode(', ', $e['extra']) . "\n";
        echo "\n";
    }
}

if ($actionableEnums === [] && $infoEnums === []) {
    echo "All matched enums are up to date.\n\n";
}

// Unmatched discovery enums
$unmatchedDiscovery = array_diff_key($discoveryEnums, $matchedDiscoveryToPhp);
if ($unmatchedDiscovery !== []) {
    echo str_repeat('─', 72) . "\n";
    echo "UNMATCHED DISCOVERY ENUMS (no PHP enum found)\n\n";

    foreach ($unmatchedDiscovery as $discoveryPath => $values) {
        $upperValues = array_values(array_filter($values, fn (string $v): bool => isUpperCase($v)));
        sort($upperValues);
        echo "  {$discoveryPath}\n";
        echo "    Values: " . implode(', ', $upperValues) . "\n";
    }

    echo "\n";
}

// ── 6. Schema property comparison (opt-in) ───────────────────────────────────

if ($showProperties) {
    echo str_repeat('─', 72) . "\n";
    echo "SCHEMA PROPERTY COMPARISON\n\n";

    $phpModels = scanPhpModelProperties($modelDir);
    $hasPropertyDiff = false;

    foreach ($phpModels as $phpClass => $phpData) {
        $schemaName = $phpData['schemaName'];
        if (!isset($schemas[$schemaName]['properties'])) {
            continue;
        }

        $discoveryProps = array_keys($schemas[$schemaName]['properties']);
        $phpProps = $phpData['properties'];

        $missingInPhp = array_values(array_diff($discoveryProps, $phpProps));
        $extraInPhp = array_values(array_diff($phpProps, $discoveryProps));

        sort($missingInPhp);
        sort($extraInPhp);

        if ($missingInPhp === [] && $extraInPhp === []) {
            continue;
        }

        $hasPropertyDiff = true;
        $relFile = str_replace($root . '/', '', $phpData['file']);
        echo "  {$phpClass} <> {$schemaName} ({$relFile})\n";

        if ($missingInPhp !== []) {
            echo "    Not in PHP: " . implode(', ', $missingInPhp) . "\n";
        }
        if ($extraInPhp !== []) {
            echo "    Extra in PHP: " . implode(', ', $extraInPhp) . "\n";
        }
        echo "\n";
    }

    if (!$hasPropertyDiff) {
        echo "  No property differences on modeled schemas.\n\n";
    }
}

// ── Summary ──────────────────────────────────────────────────────────────────

echo str_repeat('─', 72) . "\n";

if ($hasActionable) {
    echo "Result: actionable differences found.\n";
    exit(1);
}

echo "Result: no actionable differences (models are up to date).\n";
exit(0);

// ═════════════════════════════════════════════════════════════════════════════
// Helper functions
// ═════════════════════════════════════════════════════════════════════════════

function isUpperCase(string $value): bool
{
    return $value === strtoupper($value);
}

/**
 * @return array<string, array{file: string, values: list<string>}>
 */
function scanPhpEnums(string $modelDir): array
{
    $enums = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelDir));

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !str_ends_with($file->getFilename(), 'Enum.php')) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }

        if (!preg_match('/enum\s+(\w+Enum)\s*:\s*string/', $content, $m)) {
            continue;
        }

        preg_match_all("/case\s+\w+\s*=\s*'([^']+)'/", $content, $caseMatches);
        $values = $caseMatches[1];
        sort($values);

        $enums[$m[1]] = [
            'file' => $file->getPathname(),
            'values' => $values,
        ];
    }

    return $enums;
}

/**
 * @return array<string, array{file: string, schemaName: string, properties: list<string>}>
 */
function scanPhpModelProperties(string $modelDir): array
{
    $models = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelDir));

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $filename = $file->getFilename();
        if (!preg_match('/^((?:EventTicket|Flight|Generic|GiftCard|Loyalty|Offer|Transit)(?:Class|Object))\.php$/', $filename, $m)) {
            continue;
        }

        $className = $m[1];
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }

        $typeName = $className . 'Type';
        $pattern = '/@phpstan-type\s+' . preg_quote($typeName, '/') . '\s+array\s*\{/';
        if (!preg_match($pattern, $content, $tm, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        $openPos = $tm[0][1] + strlen($tm[0][0]) - 1;
        $body = extractBalancedBraces($content, $openPos);

        $props = [];
        if (preg_match_all('/(?:^|,\s*)([a-zA-Z_][a-zA-Z0-9_]*)\??\s*:/m', $body, $pm)) {
            $props = $pm[1];
        }

        $props = array_values(array_unique($props));
        sort($props);

        $models[$className] = [
            'file' => $file->getPathname(),
            'schemaName' => $className,
            'properties' => $props,
        ];
    }

    return $models;
}

function extractBalancedBraces(string $content, int $openPos): string
{
    $len = strlen($content);
    if ($openPos >= $len || $content[$openPos] !== '{') {
        return '';
    }

    $depth = 0;
    $bodyStart = $openPos + 1;

    for ($i = $openPos; $i < $len; $i++) {
        if ($content[$i] === '{') {
            $depth++;
        } elseif ($content[$i] === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($content, $bodyStart, $i - $bodyStart);
            }
        }
    }

    return '';
}

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
