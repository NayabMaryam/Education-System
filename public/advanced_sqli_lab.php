<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/mode.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_local_lab_request()) {
    http_response_code(403);
    exit('This lab is restricted to localhost.');
}

$results = [];
$error = '';

try {
    // Stage 1: Show the columns in the synthetic target query.
    $results['query_columns'] = [
        'id',
        'content',
        'created_at'
    ];

    // Stage 2: List only the synthetic lab tables.
    $stmt = $pdo->query(
        "SELECT TABLE_NAME
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME IN ('dummy_comments', 'dummy_users')
         ORDER BY TABLE_NAME"
    );
    $results['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Stage 3: Enumerate dummy_users column names.
    $stmt = $pdo->query(
        "SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'dummy_users'
         ORDER BY ORDINAL_POSITION"
    );
    $results['columns'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Stage 4: Display synthetic records only.
    $stmt = $pdo->query(
        'SELECT id, username, password_demo_value, role
         FROM dummy_users
         ORDER BY id'
    );
    $results['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Advanced synthetic lab error: ' . $e->getMessage());
    $error = 'The lab query could not be completed.';
}

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Advanced SQLi Lab | SecureStudy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 0 16px;
            background: #f1f6f6;
            color: #263238;
        }
        section {
            background: white;
            padding: 18px;
            margin: 16px 0;
            border: 1px solid #dce5e7;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 9px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            overflow-wrap: anywhere;
        }
        code {
            background: #eef2f3;
            padding: 3px 5px;
        }
    </style>
</head>
<body>
    <h1>Advanced SQLi Lab</h1>
    <p>Local synthetic-data demonstration — not a live credential database.</p>
    <p><a href="comment_search.php">Back to Comment Search</a></p>

    <?php if ($error !== ''): ?>
        <p><?= h($error) ?></p>
    <?php else: ?>
        <section>
            <h2>Stage 1: Query column structure</h2>
            <p>
                The synthetic target query returns
                <?= count($results['query_columns']) ?> columns.
            </p>
            <ol>
                <?php foreach ($results['query_columns'] as $column): ?>
                    <li><?= h($column) ?></li>
                <?php endforeach; ?>
            </ol>
        </section>

        <section>
            <h2>Stage 2: Synthetic lab tables</h2>
            <ul>
                <?php foreach ($results['tables'] as $table): ?>
                    <li><?= h($table) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section>
            <h2>Stage 3: dummy_users columns</h2>
            <ul>
                <?php foreach ($results['columns'] as $column): ?>
                    <li><?= h($column) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section>
            <h2>Stage 4: Synthetic records</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Demo value</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['records'] as $record): ?>
                        <tr>
                            <td><?= h($record['id']) ?></td>
                            <td><?= h($record['username']) ?></td>
                            <td><?= h($record['password_demo_value']) ?></td>
                            <td><?= h($record['role']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</body>
</html>