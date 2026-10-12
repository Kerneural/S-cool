<?php

// Static scanner fixture only. Do not execute this file.
function semgrepSecurityFixture(): void
{
    // ruleid: scool-php-review-eval
    eval('return 1;');

    // ruleid: scool-php-review-unserialize
    unserialize('a:0:{}');

    // ok: scool-php-review-eval
    strlen('example');

    // ok: scool-php-review-unserialize
    json_decode('{}', true);
}
