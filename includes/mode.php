
<?php

// Safe mode is the default.
if (!isset($_SESSION['app_mode'])) {
    $_SESSION['app_mode'] = 'safe';
}

function get_app_mode(): string
{
    return $_SESSION['app_mode'] ?? 'safe';
}

function is_vulnerable_mode(): bool
{
    return get_app_mode() === 'vulnerable';
}

function is_local_lab_request(): bool
{
    $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

    return in_array(
        $remoteAddress,
        ['127.0.0.1', '::1'],
        true
    );
}
