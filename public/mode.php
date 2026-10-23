
<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mode.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!verify_csrf_token()) {
    http_response_code(403);
    exit('Invalid request. Please return to the dashboard and try again.');
}

$requestedMode = $_POST['app_mode'] ?? '';

if (!in_array($requestedMode, ['safe', 'vulnerable'], true)) {
    http_response_code(400);
    exit('Invalid mode selection.');
}

// Vulnerable mode is available only from the local lab.
if ($requestedMode === 'vulnerable' && !is_local_lab_request()) {
    http_response_code(403);
    exit('Vulnerable mode is restricted to the local lab.');
}

$_SESSION['app_mode'] = $requestedMode;

header('Location: dashboard.php');
exit;
