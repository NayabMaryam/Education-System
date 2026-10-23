
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Already logged-in users should go to dashboard.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$name = '';
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verify CSRF token.
    if (!verify_csrf_token()) {
        http_response_code(403);
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        // 2. Read and validate form inputs.
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '' || strlen($name) > 100) {
            $error = 'Please enter a name (maximum 100 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // 3. Check whether this email already exists.
            $check = $pdo->prepare(
                'SELECT id FROM users WHERE email = ?'
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                // 4. Hash the password before storing it.
                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                try {
                    // 5. Create a student account.
                    $insert = $pdo->prepare(
                        'INSERT INTO users (name, email, password_hash)
                         VALUES (?, ?, ?)'
                    );

                    $insert->execute([
                        $name,
                        $email,
                        $passwordHash
                    ]);

                    // 6. Redirect to login after successful signup.
                    header('Location: login.php?registered=1');
                    exit;

                } catch (PDOException $e) {
                    // Also handle a duplicate email if another
                    // request registered it at the same time.
                    if ($e->getCode() === '23000') {
                        $error = 'This email may already be registered.';
                    } else {
                        error_log($e->getMessage());
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SecureStudy | Create Account</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f1f6f6;
            color: #263238;
        }

        .signup-card {
            width: 100%;
            max-width: 440px;
            padding: 30px;
            background: white;
            border: 1px solid #dce5e7;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        }

        h1 {
            margin-top: 0;
            color: #245b63;
        }

        label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }

        input {
            width: 100%;
            margin-top: 7px;
            padding: 12px;
            border: 1px solid #bdcbce;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            border: 0;
            border-radius: 6px;
            background: #245b63;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .error {
            padding: 10px;
            background: #fff0f0;
            color: #a32626;
            border-radius: 6px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }

        a {
            color: #245b63;
        }
    </style>
</head>

<body>
    <main class="signup-card">
        <h1>Create your account</h1>
        <p>Join SecureStudy to explore courses and discussions.</p>

        <?php if ($error !== ''): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="signup.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="name">Full name</label>
            <input
                type="text"
                id="name"
                name="name"
                maxlength="100"
                autocomplete="name"
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                autocomplete="email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="confirm_password">Confirm password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit">Create Account</button>
        </form>

        <p class="footer">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </main>
</body>
</html>
