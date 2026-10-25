
<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mode.php';

if (is_vulnerable_mode() && !is_local_lab_request()) {
    http_response_code(403);
    exit('Vulnerable mode is restricted to the local lab.');
}

$level = $_GET['level'] ?? 'easy';
$search = trim($_GET['q'] ?? '');
$results = [];
$error = '';
$sqlError = false;

$validLevels = ['easy', 'intermediate', 'advanced'];

if (!in_array($level, $validLevels, true)) {
    $level = 'easy';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['run'])) {
    try {
        if (is_vulnerable_mode()) {
            // INTENTIONALLY VULNERABLE: controlled SQL injection examples.
            if ($level === 'easy') {
                $sql = "SELECT id, title, description
                        FROM courses
                        WHERE title = '$search'
                        ORDER BY id";
                // echo '<pre>';
                // echo htmlspecialchars($sql, ENT_QUOTES, 'UTF-8');
                // echo '</pre>';
                // exit;
                $stmt = $pdo->query($sql);
            }elseif ($level === 'intermediate') {
                $sql = "SELECT id, title, description
                        FROM courses
                        WHERE id = $search
                        ORDER BY id";

                $stmt = $pdo->query($sql);
            } else {
                $sql = "SELECT id, title, description
                        FROM courses
                        WHERE title = '$search'
                        ORDER BY id";

                $stmt = $pdo->query($sql);
            }
        } else {
            // SAFE: prepared statements keep input separate from SQL.
            if ($level === 'intermediate') {
                if (!ctype_digit($search) || (int)$search < 1) {
                    $error = 'Enter a valid positive course ID.';
                } else {
                    $stmt = $pdo->prepare(
                        'SELECT id, title, description
                         FROM courses
                         WHERE id = ?
                         ORDER BY id'
                    );
                    $stmt->execute([(int)$search]);
                }
            } else {
                $stmt = $pdo->prepare(
                    'SELECT id, title, description
                     FROM courses
                     WHERE title = ?
                     ORDER BY id'
                );
                $stmt->execute([$search]);
            }
        }

        if ($error === '' && isset($stmt)) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
    $sqlError = true;
    $error = 'Database error: ' . $e->getMessage();
    }
}
function sqli_e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SecureStudy | SQL Injection Lab</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            background: #f4f7f8;
            color: #263238;
        }

        h1, h2 {
            color: #245b63;
        }

        .panel, .course {
            background: white;
            padding: 20px;
            margin: 16px 0;
            border: 1px solid #dce5e7;
            border-radius: 10px;
        }

        label, select, input, button {
            display: block;
            width: 100%;
            box-sizing: border-box;
            margin-top: 10px;
        }

        select, input, button {
            padding: 10px;
        }

        button {
            cursor: pointer;
        }

        .notice {
            padding: 12px;
            border-radius: 6px;
            background: #e8f1f2;
        }

        .error {
            color: #a32626;
        }

        a {
            color: #245b63;
        }
    </style>
</head>
<body>
    <h1>SQL Injection Demonstration Lab</h1>

    <p>
        Current mode:
        <strong><?= sqli_e(ucfirst(get_app_mode())) ?></strong>
    </p>

    <p>
        This lab uses the SecureStudy courses table only.
        Do not use real or sensitive data.
    </p>

    <section class="panel">
        <h2>Choose a demonstration</h2>

        <form method="GET" action="sqli.php">
            <label for="level">SQL Injection level</label>
            <select id="level" name="level">
                <option value="easy"
                    <?= $level === 'easy' ? 'selected' : '' ?>>
                    Easy — Direct string injection
                </option>
                <option value="intermediate"
                    <?= $level === 'intermediate' ? 'selected' : '' ?>>
                    Intermediate — Numeric condition injection
                </option>
                <option value="advanced"
                    <?= $level === 'advanced' ? 'selected' : '' ?>>
                    Advanced — UNION query injection
                </option>
            </select>

            <label for="q">
                <?php if ($level === 'intermediate'): ?>
                    Course ID or test expression
                <?php else: ?>
                    Exact course title or test input
                <?php endif; ?>
            </label>

            <input
                id="q"
                name="q"
                type="text"
                value="<?= sqli_e($search) ?>"
                placeholder="<?=
                    $level === 'intermediate'
                        ? 'Example: 1'
                        : 'Example: Information Security'
                ?>"
                required
            >

            <button type="submit" name="run" value="1">
                Run Demonstration
            </button>
        </form>
    </section>

    <section class="panel">
        <h2>How this level works</h2>

        <?php if ($level === 'easy'): ?>
            <p>
                The vulnerable version inserts the submitted title
                directly into a quoted SQL condition. The safe version
                uses a prepared statement.
            </p>
        <?php elseif ($level === 'intermediate'): ?>
            <p>
                The vulnerable version inserts the supplied value
                directly into a numeric course-ID condition. The safe
                version accepts only a positive numeric ID and uses
                a prepared statement.
            </p>
        <?php else: ?>
            <p>
                The advanced exercise is intended to demonstrate how
                UNION can combine compatible SELECT results. The safe
                version treats the entire input as a course title,
                rather than SQL syntax.
            </p>
            <p>
                Use only a controlled test input for this exercise.
            </p>
        <?php endif; ?>

        <p class="notice">
            Safe Mode should return results only for the supplied
            course title or valid course ID. Input must never become
            executable SQL.
        </p>
    </section>

    <?php if ($error !== ''): ?>
        <p class="error"><?= sqli_e($error) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['run']) && $error === ''): ?>
        <h2>Results</h2>

        <?php if (count($results) === 0): ?>
            <p>No matching courses found.</p>
        <?php else: ?>
            <?php foreach ($results as $course): ?>
                <article class="course">
                    <h3><?= sqli_e($course['title']) ?></h3>
                    <p>
                        <strong>Course ID:</strong>
                        <?= sqli_e($course['id']) ?>
                    </p>
                    <p><?= sqli_e($course['description']) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
