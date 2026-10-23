
<?php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query(
    'SELECT id, title, description FROM courses ORDER BY title'
);

$courses = $stmt->fetchAll();
?>
<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/db.php';

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
    </style>
</head>
<body>
    <h1>Education Dashboard</h1>
    <p>Welcome to your student dashboard.</p>
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
