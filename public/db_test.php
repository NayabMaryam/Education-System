
<?php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query(
    'SELECT COUNT(*) AS total FROM courses'
);

$result = $stmt->fetch();

echo 'Database connected successfully!<br>';
echo 'Courses found: ' . (int) $result['total'];
