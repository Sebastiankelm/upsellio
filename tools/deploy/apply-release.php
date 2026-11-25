<?php
// Runs only over authenticated SSH, outside the webroot. PHP >= 7.4.
declare(strict_types=1);
umask(0077);
function require_ok($condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
function cmd(array $parts): string {
    $output = [];
    exec(implode(' ', array_map('escapeshellarg', $parts)) . ' 2>&1', $output, $status);
    require_ok($status === 0, 'Command failed: ' . $parts[0]);
    return implode("\n", $output);
}
function atomic_copy(string $source, string $target, int $mode): void {
    $temp = tempnam(dirname($target), '.ups-deploy-');
    require_ok($temp !== false, 'Cannot create temporary file');
    try {
        require_ok(copy($source, $temp), 'Copy failed');
        require_ok(chmod($temp, $mode), 'chmod failed');
        require_ok(hash_file('sha256', $source) === hash_file('sha256', $temp), 'Copy hash mismatch');
        require_ok(rename($temp, $target), 'Atomic replace failed');
    } finally {
        if (is_file($temp)) { unlink($temp); }
    }
}
function safe_target(string $root, string $relative): string {
    $target = $root . '/' . $relative;
    require_ok(is_file($target) && !is_link($target) && realpath($target) === $target, 'Missing or symlinked target: ' . $relative);
    return $target;
}
function smoke(string $root, string $head): void {
    cmd(['wp', '--path=' . dirname($root, 3), 'cache', 'flush']);
    foreach (['/', '/marketing-google-ads/', '/marketing-meta-ads/', '/tworzenie-stron-internetowych/', '/kontakt/'] as $path) {
        $status = cmd(['curl', '--silent', '--show-error', '--fail', '--location', '--max-redirs', '3',
            '--max-time', '25', '--output', '/dev/null', '--write-out', '%{http_code}',
            'https://upsellio.pl' . $path . '?upsellio_release=' . $head]);
        require_ok($status === '200', 'HTTP smoke check failed: ' . $path);
    }
}
$allowed = [
    'assets/css/upsellio.css', 'assets/js/upsellio-ui.js', 'assets/js/upsellio.js',
    'front-page.php', 'functions.php', 'header.php', 'home.php', 'single.php',
    'inc/crm.php', 'inc/form-component.php', 'page-kontakt.php',
    'inc/technical-seo.php', 'inc/seo-indexing-fixes.php', 'inc/cities-seed.php', 'inc/definitions-seed.php',
    'page-marketing-google-ads.php', 'page-marketing-meta-ads.php', 'page-tworzenie-stron-internetowych.php',
];
$initial = 'd8b359ae80d1f8021cbe904e0bf43fa6115ac056';
$home = realpath((string) getenv('HOME'));
$store = $home . '/.upsellio-deploy';
$root = realpath($home . '/domains/upsellio.pl/public_html/wp-content/themes/upsellio');
$lock = null;
$backup = null;
$changed = [];
$committed = false;
try {
    require_ok($home !== false && $root !== false && strpos($root, $home . '/') === 0, 'Unexpected production directory');
    require_ok(is_dir($store) && !is_link($store) && realpath($store) === $store, 'Unsafe deployment directory');
    $lock = fopen($store . '/lock', 'c');
    require_ok($lock !== false && flock($lock, LOCK_EX | LOCK_NB), 'Another deployment is active');
    require_ok(!file_exists($store . '/pending.json'), 'Previous release interrupted; inspect pending.json before continuing');
    $bundle = realpath($argv[1] ?? '');
    require_ok($bundle !== false && strpos($bundle, $store . '/incoming/') === 0, 'Invalid bundle location');
    $manifest = json_decode((string) file_get_contents($bundle . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $base = $manifest['base'] ?? '';
    $head = $manifest['head'] ?? '';
    require_ok(is_string($base) && is_string($head) && preg_match('/^[a-f0-9]{40}$/D', $base) && preg_match('/^[a-f0-9]{40}$/D', $head), 'Invalid release identifiers');
    $state = is_file($store . '/deployed-sha') ? trim((string) file_get_contents($store . '/deployed-sha')) : $initial;
    require_ok($state === $base, 'Release base changed; re-run workflow');
    require_ok(is_array($manifest['files'] ?? null), 'Invalid file list');
    $seen = [];
    $plan = [];
    foreach ($manifest['files'] as $record) {
        $path = $record['path'] ?? '';
        require_ok(in_array($path, $allowed, true) && !isset($seen[$path]), 'Unapproved or duplicate path');
        $seen[$path] = true;
        $target = safe_target($root, $path);
        $source = $bundle . '/files/' . $path;
        require_ok(is_file($source) && !is_link($source) && realpath($source) === $source, 'Missing candidate: ' . $path);
        $new = hash_file('sha256', $source);
        $old = hash_file('sha256', $target);
        require_ok($new === ($record['after'] ?? null), 'Candidate hash mismatch: ' . $path);
        require_ok($old === ($record['before'] ?? null) || $old === $new, 'Production drift: ' . $path);
        if (substr($path, -4) === '.php') { cmd([PHP_BINARY, '-n', '-l', $source]); }
        if ($old !== $new) {
            $plan[$path] = ['target' => $target, 'source' => $source, 'mode' => fileperms($target) & 0777, 'before' => $old, 'after' => $new];
        }
    }
    if (($argv[2] ?? '') === 'check') {
        echo 'Preflight passed: ' . count($plan) . " changes\n";
        exit(0);
    }
    require_ok(($argv[2] ?? '') === 'apply', 'Expected check or apply');
    // Check dependencies before touching production.
    cmd(['wp', '--info']);
    cmd(['curl', '--version']);
    $backup = $store . '/backup-' . $head . '-' . bin2hex(random_bytes(6));
    require_ok(mkdir($backup, 0700), 'Cannot create backup');
    foreach ($plan as $path => $item) {
        $saved = $backup . '/' . $path;
        if (!is_dir(dirname($saved))) { require_ok(mkdir(dirname($saved), 0700, true), 'Cannot create backup directory'); }
        require_ok(copy($item['target'], $saved) && hash_file('sha256', $saved) === $item['before'], 'Backup failed: ' . $path);
    }
    require_ok(file_put_contents($backup . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT)) !== false, 'Cannot write backup manifest');
    require_ok(file_put_contents($store . '/pending.json', json_encode(['base'=>$base, 'head'=>$head, 'backup'=>$backup, 'paths'=>array_keys($plan)])) !== false, 'Cannot write release journal');
    foreach ($plan as $path => $item) {
        require_ok(hash_file('sha256', $item['target']) === $item['before'], 'Concurrent file change: ' . $path);
        atomic_copy($item['source'], $item['target'], $item['mode']);
        $changed[$path] = $item;
        require_ok(hash_file('sha256', $item['target']) === $item['after'], 'Read-back failed: ' . $path);
    }
    if ($plan) { smoke($root, $head); }
    $tempState = $store . '/deployed-sha.tmp';
    require_ok(file_put_contents($tempState, $head . "\n") === 41 && rename($tempState, $store . '/deployed-sha'), 'Cannot record deployed revision');
    $committed = true;
    require_ok(unlink($store . '/pending.json'), 'Cannot clear release journal');
    echo 'Deployed ' . $head . ': ' . count($changed) . " changed files\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    if (!$committed && $backup !== null && is_file($store . '/pending.json')) {
        try {
            foreach (array_reverse($changed, true) as $path => $item) {
                require_ok(hash_file('sha256', $item['target']) === $item['after'], 'Concurrent modification during rollback: ' . $path);
                atomic_copy($backup . '/' . $path, $item['target'], $item['mode']);
            }
            if ($changed) { cmd(['wp', '--path=' . dirname($root, 3), 'cache', 'flush']); }
            require_ok(unlink($store . '/pending.json'), 'Cannot clear journal after rollback');
            fwrite(STDERR, "Rollback completed; previous revision retained.\n");
        } catch (Throwable $rollback) {
            fwrite(STDERR, 'Rollback needs operator intervention: ' . $rollback->getMessage() . "\n");
        }
    }
    exit(1);
} finally {
    if (is_resource($lock)) { fclose($lock); }
}
