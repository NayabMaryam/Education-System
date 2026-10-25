

<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mode.php';

$stmt = $pdo->query(
    'SELECT id, title, description FROM courses ORDER BY title'
);

$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudyBoard | Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            background: #f4f7f8;
            color: #263238;
        }

        h1 {
            color: #245b63;
        }

        .course {
            background: white;
            padding: 18px;
            margin: 14px 0;
            border-radius: 10px;
            border: 1px solid #dce5e7;
        }
        
        .mode-panel {
            background: white;
            border: 1px solid #dce5e7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .mode-panel label,
        .mode-panel select,
        .mode-panel button {
            display: block;
            width: 100%;
            box-sizing: border-box;
            margin-top: 10px;
        }

        .mode-panel select,
        .mode-panel button {
            padding: 10px;
        }

    </style>
</head>
<body>
    <h1>Education Dashboard</h1>
    <p>Welcome to your student dashboard.</p>
    <p><a href="personalized_welcome.php">Personalized Welcome</a></p>
    
    <div class="dashboard-card">
        <h2>Course Discussions</h2>
        <p><a href="search.php">Course Search</a></p>
        <p><a href="comments.php">Course Discussions</a></p>
    </div>

    <div class="dashboard-card">
        <h2>Security Testing Lab</h2>
        <p>
            <a href="sqli.php">SQL Injection Demonstrations</a>
        </p>
    </div>

        <p>
            Current mode:
            <strong>
                <?= htmlspecialchars(
                    ucfirst(get_app_mode()),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>
        </p>

        <form method="POST" action="mode.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    csrf_token(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <label for="app_mode">Choose application mode</label>

            <select id="app_mode" name="app_mode">
                <option
                    value="safe"
                    <?= get_app_mode() === 'safe' ? 'selected' : '' ?>
                >
                    Safe Mode
                </option>

                <option
                    value="vulnerable"
                    <?= get_app_mode() === 'vulnerable' ? 'selected' : '' ?>
                >
                    Vulnerable Mode
                </option>
            </select>

            <button type="submit">Switch Mode</button>
        </form>
    </section>

    <h2>Available Courses</h2>

    <?php foreach ($courses as $course): ?>
        <article class="course">
            <h3><?= htmlspecialchars(
                $course['title'],
                ENT_QUOTES,
                'UTF-8'
            ) ?></h3>

            <p><?= htmlspecialchars(
                $course['description'],
                ENT_QUOTES,
                'UTF-8'
            ) ?></p>
        </article>
    <?php endforeach; ?>
        <form method="POST" action="logout.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                csrf_token(),
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
        <button type="submit">Logout</button>
    </form>
</body>
</html>
