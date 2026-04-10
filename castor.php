<?php

declare(strict_types=1);

use Castor\Attribute\AsTask;

use function Castor\PHPQa\php_cs_fixer;
use function Castor\PHPQa\phpstan;
use function Castor\run;

/**
 * PHP binary for spawning spec scripts. Castor's native CLI can run with an empty PHP_BINARY.
 *
 * @return non-empty-string
 */
function spec_tools_php(): string
{
    if (\PHP_BINARY !== '') {
        return \PHP_BINARY;
    }

    $fromEnv = getenv('PHP_BINARY');
    if (\is_string($fromEnv) && $fromEnv !== '') {
        return $fromEnv;
    }

    return 'php';
}

#[AsTask('cs:check', namespace: 'qa', description: 'Check for coding standards without fixing them')]
function qa_cs_check(): void
{
    php_cs_fixer(['fix', '--config', __DIR__ . '/.php-cs-fixer.php', '--dry-run', '--diff'], '3.85.1', []);
}

#[AsTask('cs:fix', namespace: 'qa', description: 'Fix all coding standards', aliases: ['cs'])]
function qa_cs_fix(): void
{
    php_cs_fixer(['fix', '--config', __DIR__ . '/.php-cs-fixer.php', '-v'], '3.85.1', []);
}

#[AsTask('phpstan', namespace: 'qa', description: 'Run PHPStan for static analysis', aliases: ['phpstan'])]
function qa_phpstan(bool $generateBaseline = false): void
{
    $params = ['analyse', '--configuration', __DIR__ . '/phpstan.neon', '--memory-limit=-1', '-v'];
    if ($generateBaseline) {
        $params[] = '--generate-baseline';
    }

    phpstan($params, '2.1.17');
}

#[AsTask('tests', description: 'Run PHPUnit')]
function qa_test(): void
{
    run([__DIR__ . '/vendor/bin/phpunit']);
}

#[AsTask('check:apple', namespace: 'spec', description: 'Compare Apple pass phpstan keyset to tools/spec/apple-pass-keyset.json')]
function spec_check_apple(): void
{
    run([spec_tools_php(), __DIR__ . '/tools/spec/apple-pass-keyset.php', 'check']);
}

#[AsTask('check:google', namespace: 'spec', description: 'Compare live Google Wallet discovery revision to tools/spec/google-wallet-baseline.json')]
function spec_check_google(): void
{
    run([spec_tools_php(), __DIR__ . '/tools/spec/google-wallet-spec.php', 'check']);
}

#[AsTask('baseline:apple', namespace: 'spec', description: 'Regenerate tools/spec/apple-pass-keyset.json from Apple Model phpstan types')]
function spec_baseline_apple(): void
{
    run([spec_tools_php(), __DIR__ . '/tools/spec/apple-pass-keyset.php', 'baseline']);
}

#[AsTask('baseline:google', namespace: 'spec', description: 'Update tools/spec/google-wallet-baseline.json from live discovery (revision + version)')]
function spec_baseline_google(): void
{
    run([spec_tools_php(), __DIR__ . '/tools/spec/google-wallet-spec.php', 'baseline']);
}

#[AsTask('diff:google', namespace: 'spec', description: 'Diff live Google Wallet discovery enums/properties against PHP models')]
function spec_diff_google(bool $properties = false): void
{
    $args = [spec_tools_php(), __DIR__ . '/tools/spec/google-wallet-diff.php'];
    if ($properties) {
        $args[] = '--properties';
    }
    run($args);
}

#[AsTask('check:samsung', namespace: 'spec', description: 'Compare Samsung Wallet phpstan keyset to tools/spec/samsung-wallet-keyset.json')]
function spec_check_samsung(): void
{
    run([spec_tools_php(), __DIR__ . '/tools/spec/samsung-wallet-keyset.php', 'check']);
}

#[AsTask('baseline:samsung', namespace: 'spec', description: 'Regenerate tools/spec/samsung-wallet-keyset.json from Samsung Model phpstan types')]
function spec_baseline_samsung(): void
{
    run([spec_tools_php(), __DIR__ . '/tools/spec/samsung-wallet-keyset.php', 'baseline']);
}
