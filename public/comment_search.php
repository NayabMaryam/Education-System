
<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/mode.php';
require_once __DIR__ . '/../includes/db.php';

// Vulnerable demonstrations are restricted to the local lab.
if (is_vulnerable_mode() && !is_local_lab_request()) {
    http_response_code(403);
    exit('Vulnerable mode is restricted to the local lab.');
}

$search = trim($_GET['q'] ?? '');
$searchType = $_GET['type'] ?? 'keyword';

$comments = [];
$error = '';

if (!in_array($searchType, ['keyword', 'id'], true)) {
    $searchType = 'keyword';
}

if ($search !== '') {
    try {
        if (is_vulnerable_mode()) {

            // EASY: string-based SQL injection in keyword search.
            if ($searchType === 'keyword') {
                $sql = "SELECT comments.id, comments.content,
                               comments.created_at, users.name,
                               courses.title
                        FROM comments
                        JOIN users ON users.id = comments.user_id
                        JOIN courses ON courses.id = comments.course_id
                        WHERE comments.content LIKE '%$search%'
                        ORDER BY comments.created_at DESC";

                // INTERMEDIATE: numeric SQL injection in comment ID lookup.
            } else {
                if (!ctype_digit($search)) {
                    // Allow SQL expressions in Vulnerable Mode for the lab.
                    $sql = "SELECT comments.id, comments.content,
                                   comments.created_at, users.name,
                                   courses.title
                            FROM comments
                            JOIN users ON users.id = comments.user_id
                            JOIN courses ON courses.id = comments.course_id
                            WHERE comments.id = $search
                            ORDER BY comments.created_at DESC";
                } else {
                    $sql = "SELECT comments.id, comments.content,
                                   comments.created_at, users.name,
                                   courses.title
                            FROM comments
                            JOIN users ON users.id = comments.user_id
                            JOIN courses ON courses.id = comments.course_id
                            WHERE comments.id = $search
                            ORDER BY comments.created_at DESC";
                }
            }

            $stmt = $pdo->query($sql);
            $comments = $stmt->fetchAll();

        } else {
            // SAFE MODE: use prepared statements.
            if ($searchType === 'id') {
                if (!ctype_digit($search) || (int) $search < 1) {
                    $error = 'Please enter a valid comment ID.';
                } else {
                    $stmt = $pdo->prepare(
                        'SELECT comments.id, comments.content,
                                comments.created_at, users.name,
                                courses.title
                         FROM comments
                         JOIN users ON users.id = comments.user_id
                         JOIN courses ON courses.id = comments.course_id
                         WHERE comments.id = ?
                         ORDER BY comments.created_at DESC'
                    );

                    $stmt->execute([(int) $search]);
                    $comments = $stmt->fetchAll();
                }
            } else {
                $stmt = $pdo->prepare(
                    'SELECT comments.id, comments.content,
                            comments.created_at, users.name,
                            courses.title
                     FROM comments
                     JOIN users ON users.id = comments.user_id
                     JOIN courses ON courses.id = comments.course_id
                     WHERE comments.content LIKE ?
                     ORDER BY comments.created_at DESC'
                );

                $stmt->execute(['%' . $search . '%']);
                $comments = $stmt->fetchAll();
            }
        }
    } catch (PDOException $e) {
        error_log('SecureStudy comment search error: ' . $e->getMessage());
        $error = 'Search could not be completed. Please check your input.';
    }
}

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
    <title>SecureStudy | Comment Search</title>
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

        label, input, select, button {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        input, select {
            padding: 10px;
            margin: 8px 0 16px;
        }

        button {
            padding: 12px;
            background: #245b63;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .meta {
            color: #607477;
            font-size: 14px;
        }

        .error {
            color: #a32626;
        }

        .comment-text {
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        a {
            color: #245b63;
        }
        
        .mode-safe {
            color: #176b38;
            background: #e4f5e9;
            padding: 5px 9px;
            border-radius: 5px;
            font-weight: bold;
        }

        .mode-vulnerable {
            color: #a32626;
            background: #fde8e8;
            padding: 5px 9px;
            border-radius: 5px;
            font-weight: bold;
        }

    </style>
</head>
<body>
<main>
    
<
<body>
<main>
    <h1>Comment Search</h1>
    <p>
        <strong>Current Mode:</strong>
        <?php if (is_vulnerable_mode()): ?>
            <span class="mode-vulnerable">Vulnerable Mode</span>
        <?php else: ?>
            <span class="mode-safe">Safe Mode</span>
        <?php endif; ?>
        &nbsp;|&nbsp;
        <a href="dashboard.php">Back to dashboard</a>
    </p>
    <p>Find comments from course discussions.</p>

    <section class="panel">
        <form method="GET" action="comment_search.php">
            <label for="type">Search by</label>
            <select id="type" name="type">
                <option value="keyword"
                    <?= $searchType === 'keyword' ? 'selected' : '' ?>>
                    Comment keyword
                </option>
                <option value="id"
                    <?= $searchType === 'id' ? 'selected' : '' ?>>
                    Comment ID
                </option>
            </select>

            <label for="q">Search term</label>
            <input
                id="q"
                name="q"
                type="text"
                value="<?= e($search) ?>"
                maxlength="200"
                required
            >

            <button type="submit">Search Comments</button>
        </form>
    </section>

    <?php if ($error !== ''): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($search !== '' && $error === ''): ?>
        <h2>Search Results</h2>

        <?php if (!$comments): ?>
            <p>No matching comments found.</p>
        <?php endif; ?>

        <?php foreach ($comments as $comment): ?>
            <article class="comment">
                <h3><?= e($comment['title']) ?></h3>
                <p class="meta">
                    Comment ID: <?= (int) $comment['id'] ?><br>
                    Posted by <?= e($comment['name']) ?><br>
                    <?= e($comment['created_at']) ?>
                </p>
                <p class="comment-text">
                    <?= e($comment['content']) ?>
                </p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
