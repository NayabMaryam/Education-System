<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/mode.php';

// Vulnerable demonstrations are restricted to the local lab.
if (is_vulnerable_mode() && !is_local_lab_request()) {
    http_response_code(403);
    exit('Vulnerable mode is restricted to the local lab.');
}

$vulnerableMode = is_vulnerable_mode() ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudyBoard | Personalized Welcome</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f6f6;
            color: #263238;
            margin: 0;
            padding: 30px 16px;
        }

        main {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 24px;
            border: 1px solid #dce5e7;
            border-radius: 10px;
        }

        input, button {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
        }

        button {
            background: #245b63;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        #messageOutput {
            padding: 12px;
            background: #f1f6f6;
            border-radius: 6px;
            overflow-wrap: anywhere;
        }

        a {
            color: #245b63;
        }
    </style>
</head>

<body>
<main>
    <h1>Personalized Welcome</h1>

    <p><a href="dashboard.php">Back to dashboard</a></p>

    <p>
        Current mode:
        <strong>
            <?= $vulnerableMode === 'true' ? 'Vulnerable' : 'Safe' ?>
        </strong>
    </p>

    <form method="GET" action="personalized_welcome.php">
        <label for="message">Enter a Welcome Message</label>

        <input
            type="text"
            id="message"
            name="message"
            placeholder="Enter a message to display"
        >

        <button type="submit">Display Message</button>
    </form>

    <h2 id="messageheading">Hello, Student!</h2>

    <div id="messageOutput">Your message will appear here.</div>
</main>

<script>
    alert("Working");
    const vulnerableMode = <?php echo $vulnerableMode; ?>;

    const params = new URLSearchParams(window.location.search);
    const message = params.get('message');

    const output = document.getElementById('messageheading');
    const input = document.getElementById('message');

    if (message !== null) {
        input.value = message;

        if (vulnerableMode) {
            output.innerHTML = message;
        } else {
            output.textContent = message;
        }
    }
</script>

</body>
</html>
