
<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/mode.php';

if (is_vulnerable_mode() && !is_local_lab_request()) {
    http_response_code(403);
    exit('Vulnerable mode is restricted to the local lab.');
}

$query = trim($_GET['q'] ?? '');

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SecureStudy | Course Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f6f6;
            color: #263238;
            margin: 0;
            padding: 30px 16px;
        }
        main {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 24px;
            border: 1px solid #dce5e7;
            border-radius: 10px;
        }
        input, button {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
        }
        button {
            background: #245b63;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        a { color: #245b63; }
    </style>
</head>
<body>
<main>
    <h1>Course Search</h1>
    <p><a href="dashboard.php">Back to dashboard</a></p>

    <form method="GET" action="search.php">
        <label for="q">Search for a course</label>
        <input
            type="text"
            id="q"
            name="q"
            value="<?= e($query) ?>"
            placeholder="Enter a course name"
        >
        <button type="submit">Search</button>
    </form>

    <?php if ($query !== ''): ?>
        <h2>Search results for:</h2>

        <?php if (is_vulnerable_mode()): ?>
            <p><?= $query ?></p>
        <?php else: ?>
            <p><?= e($query) ?></p>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
