
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        http_response_code(403);
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare(
            'SELECT id, name, email, password_hash, role
             FROM users
             WHERE email = ?
             LIMIT 1'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify(
            $password,
            $user['password_hash']
        )) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Rotate the CSRF token after login.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SecureStudy | Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f6f6;
            color: #263238;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }

        .login-card {
            background: white;
            width: min(360px, calc(100% - 48px));
            padding: 28px;
            border-radius: 12px;
            border: 1px solid #dce5e7;
        }

        label, input, button {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        input {
            padding: 11px;
            margin: 7px 0 16px;
        }

        button {
            padding: 12px;
            background: #245b63;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            color: #a32626;
        }
    </style>
</head>
<body>
    <main class="login-card">
        <!-- LOGIN FILE CHECK -->
        <h1>StudyBoard</h1>
        <p>Sign in to your student account.</p>

        <?php if ($error !== ''): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
            <!-- MARKER A -->
        <form method="POST" action="login.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    csrf_token(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
            <!-- MARKER B -->
            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Login</button>
        </form>
        <!-- MARKER C -->
    </main>
</body>
</html>
