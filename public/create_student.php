
<?php
require_once __DIR__ . '/../includes/db.php';

$name = 'Demo Student';
$email = 'student@example.test';
$password = 'StudyDemo@2026';

// Generate a secure password hash.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Avoid creating the same demo account twice.
$check = $pdo->prepare(
    'SELECT id FROM users WHERE email = ?'
);
$check->execute([$email]);

if ($check->fetch()) {
    exit('Demo student already exists. You can use the login page.');
}

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role)
     VALUES (?, ?, ?, ?)'
);

$stmt->execute([
    $name,
    $email,
    $passwordHash,
    'student'
]);

echo 'Demo student created successfully.<br>';
echo 'Email: ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
