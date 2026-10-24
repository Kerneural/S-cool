<?php

// Standalone gate: no application bootstrap, credentials or database access.
// PHP 8.2.35 fixes CVE-2026-109618 and CVE-2026-109619. Revisit this
// branch-specific floor when deliberately upgrading the runtime to another minor.
if (PHP_VERSION_ID < 80235 || PHP_VERSION_ID >= 80300) {
    fwrite(STDERR, '[FAIL] Expected patched PHP 8.2 (>= 8.2.35); found '.PHP_VERSION.".\n");
    exit(1);
}

if (filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN)) {
    fwrite(STDERR, "[FAIL] session.upload_progress.enabled must be Off before request parsing.\n");
    exit(1);
}

if (! filter_var(ini_get('zend.exception_ignore_args'), FILTER_VALIDATE_BOOLEAN)) {
    fwrite(STDERR, "[FAIL] zend.exception_ignore_args must be On.\n");
    exit(1);
}

echo '[PASS] PHP '.PHP_VERSION."; upload progress Off; exception arguments hidden.\n";
