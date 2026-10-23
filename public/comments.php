
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/mode.php';
require_once __DIR__ . '/../includes/db.php';

// Vulnerable demonstrations are restricted to the local lab.
if (is_vulnerable_mode() && !is_local_lab_request()) {
    http_response_code(403);
    exit('Vulnerable mode is restricted to the local lab.');
}
$message = '';
$error = '';

$coursesStmt = $pdo->query(
    'SELECT id, title FROM courses ORDER BY title'
);
$courses = $coursesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        http_response_code(403);
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $courseId = filter_input(
            INPUT_POST,
            'course_id',
            FILTER_VALIDATE_INT
        );

        $content = trim($_POST['content'] ?? '');

        if (!$courseId || $content === '') {
            $error = 'Please select a course and enter a comment.';
        } elseif (strlen($content) > 2000) {
            $error = 'Comment must be 2000 characters or fewer.';
        } else {
            $checkCourse = $pdo->prepare(
                'SELECT id FROM courses WHERE id = ?'
            );
            $checkCourse->execute([$courseId]);

            if (!$checkCourse->fetch()) {
                $error = 'Please select a valid course.';
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO comments (user_id, course_id, content)
                     VALUES (?, ?, ?)'
                );

                $insert->execute([
                    $_SESSION['user_id'],
                    $courseId,
                    $content
                ]);

                header('Location: comments.php?posted=1');
                exit;
            }
        }
    }
}

if (isset($_GET['posted'])) {
    $message = 'Your comment was posted successfully.';
}

$commentsStmt = $pdo->query(
    'SELECT comments.id, comments.content, comments.created_at,
            users.name, courses.title
     FROM comments
     JOIN users ON users.id = comments.user_id
     JOIN courses ON courses.id = comments.course_id
     ORDER BY comments.created_at DESC'
);

$comments = $commentsStmt->fetchAll();

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
    <title>SecureStudy | Course Discussions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f6f6;
            color: #263238;
            margin: 0;
            padding: 30px 16px;
        }

        main {
            max-width: 760px;
            margin: auto;
        }

        .panel, .comment {
            background: white;
            padding: 20px;
            margin-bottom: 18px;
            border: 1px solid #dce5e7;
            border-radius: 10px;
        }

        label, select, textarea, button {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        select, textarea {
            padding: 10px;
            margin: 8px 0 16px;
        }

        textarea {
            min-height: 100px;
        }

        button {
            padding: 12px;
            background: #245b63;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .success {
            color: #176b38;
        }

        .error {
            color: #a32626;
        }

        .meta {
            color: #607477;
            font-size: 14px;
        }

        .comment-text {
            overflow-wrap: anywhere;
        }

        a {
            color: #245b63;
        }
    </style>
</head>
<body>
<main>
    <h1>Course Discussions</h1>
    <p><a href="dashboard.php">Back to dashboard</a></p>

    <section class="panel">
        <h2>Write a comment</h2>

        <?php if ($message !== ''): ?>
            <p class="success"><?= e($message) ?></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="comments.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="course_id">Select course</label>
            <select id="course_id" name="course_id" required>
                <option value="">Choose a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= (int) $course['id'] ?>">
                        <?= e($course['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="content">Your comment</label>
            <textarea
                id="content"
                name="content"
                maxlength="2000"
                required
            ></textarea>

            <button type="submit">Post Comment</button>
        </form>
    </section>

    <h2>Recent comments</h2>

    <?php if (!$comments): ?>
        <p>No comments yet. Start the discussion!</p>
    <?php endif; ?>

    <?php foreach ($comments as $comment): ?>
        <article class="comment">
            <h3><?= e($comment['title']) ?></h3>
            <p class="meta">
                Posted by <?= e($comment['name']) ?>
                · <?= e($comment['created_at']) ?>
            </p>
            <?php if (is_vulnerable_mode()): ?>
                <p class="comment-text"><?= $comment['content'] ?></p>
            <?php else: ?>
                <p class="comment-text"><?= e($comment['content']) ?></p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</main>
</body>
</html>
