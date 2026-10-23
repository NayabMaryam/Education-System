# StudyBoard-Application Security Lab

## 1. Project Overview

**StudyBoard** is a PHP and MySQL-based educational web application developed to demonstrate common web application security vulnerabilities and their corresponding defenses.

The application provides two modes:

- **Vulnerable Mode:** Demonstrates selected security vulnerabilities in a controlled local testing environment.
- **Safe Mode:** Demonstrates security protections designed to prevent those vulnerabilities.

The project is intended for educational purposes and controlled security testing on localhost.

## 2. Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Database Access:** PHP Data Objects (PDO)
- **Local Development Environment:** MAMP
- **Version Control:** Git and GitHub

## 3. Implemented Features

### Authentication

- User signup and login.
- Password hashing and verification.
- Session-based authentication.
- Logout functionality.
- CSRF token generation and verification for protected requests.

### Safe and Vulnerable Modes

The application allows authenticated users to switch between Safe Mode and Vulnerable Mode.

- Safe Mode is the default mode.
- The selected mode is stored in the PHP session.
- Vulnerable demonstrations are restricted to local requests.
- Mode selection is handled through a POST request with CSRF verification.

**Important:** Vulnerable Mode is intended only for local security demonstrations and must not be exposed to the public internet.

### Reflected Cross-Site Scripting (XSS)

The search page displays a value provided through a URL query parameter.

- **Vulnerable Mode:** The supplied value is rendered without HTML escaping.
- **Safe Mode:** The supplied value is escaped using PHP's `htmlspecialchars()` function.

This demonstrates how reflected XSS can occur when untrusted input is inserted into HTML without appropriate output encoding.

### Stored Cross-Site Scripting (XSS)

The Course Discussion page allows authenticated users to submit comments stored in the database.

- **Vulnerable Mode:** Comment content is rendered without HTML escaping.
- **Safe Mode:** Comment content is escaped before being displayed.

This demonstrates how stored XSS can occur when malicious content is saved and later rendered to other users.

### DOM-Based Cross-Site Scripting (DOM XSS)

The Welcome Message page reads a message from the URL and updates a heading through JavaScript.

- **Vulnerable Mode:** The message is inserted using `innerHTML`.
- **Safe Mode:** The message is inserted using `textContent`.

This demonstrates the security risks of inserting untrusted input into the DOM using an unsafe JavaScript sink.

### Cross-Site Request Forgery (CSRF)

A controlled local HTML test page submits a comment to the StudyBoard application without including a CSRF token.

- **Vulnerable Mode:** The comment submission skips CSRF token verification.
- **Safe Mode:** The server verifies the submitted CSRF token before processing the request.

The demonstration is intended to show why state-changing requests need appropriate CSRF protection. Actual test results should be recorded with screenshots and observations.

## 4. Project Structure

The current project is organized around the following files and directories:

```text
Education-System/
├── public/
│   ├── login.php
│   ├── signup.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── comments.php
│   ├── search.php
│   ├── mode.php
│   └── personalized_welcome.php
├── includes/
│   ├── db.php
│   ├── auth.php
│   └── mode.php
├── modes/
│   ├── vulnerable/
│   └── safe/
├── database/
├── tests/
├── evidence/
│   ├── vulnerable/
│   └── safe/
└── README.md
```

Some folders may still be under development. Update this structure as additional files are implemented.

## 5. Database Setup

The application uses a MySQL database named:

`study_db`

The current database includes the following tables:

- **users:** Stores user account details and password hashes.
- **courses:** Stores course information.
- **comments:** Stores course discussion comments linked to users and courses.

### Setup Steps

1. Start the Apache and MySQL services in MAMP.
2. Open phpMyAdmin.
3. Create the database `study_db`.
4. Import the project's database SQL file when it is available, or create the required tables using the current database schema.
5. Check the connection settings in `includes/db.php`.
6. Ensure the PHP application can connect to the database.

> The final `database/study_db.sql` file and its setup instructions should be added or updated when the database export and seed data are ready.

## 6. Running the Application

1. Place the project inside the MAMP web root, using the current project path:

   `D:\MAMP\htdocs\Education-System\`

2. Start Apache and MySQL in MAMP.
3. Ensure the `study_db` database exists and the database connection settings are correct.
4. Open the application in your browser:

   `http://localhost/Education-System/public/login.php`

5. Log in with a seeded test account if it exists in your database.
6. Open the dashboard and select the required application mode.
7. Use the Search, Course Discussion, and Welcome Message pages to test the implemented XSS demonstrations.
8. Use the separate local CSRF test page to test comment submission in both modes.

Do not assume demo credentials are available unless they have been seeded in the database.

## 7. Security Testing

The following demonstrations have been implemented and tested during development:

| Test Area | Vulnerable Mode | Safe Mode |
|---|---|---|
| Reflected XSS | Unescaped search input | HTML-escaped output |
| Stored XSS | Unescaped stored comments | HTML-escaped comments |
| DOM XSS | `innerHTML` used with URL input | `textContent` used with URL input |
| CSRF | Token verification skipped for comment submission | Token verification required |

The CSRF results should be documented according to the actual browser and database observations. Keep screenshots for both modes where possible.

### Evidence

Store test screenshots in the relevant evidence directories:

- `evidence/vulnerable/`
- `evidence/safe/`

Maintain a testing record containing:

- Test ID
- Attack type
- Application mode
- Test input or action
- Expected result
- Actual result
- Pass/fail status
- Screenshot path
- Additional notes

## 8. Current Limitations and Pending Work

The following items remain to be completed or verified:

- Easy, intermediate, and advanced SQL Injection demonstrations for MySQL.
- Safe SQL Injection defenses using parameterized queries and appropriate input handling.
- Final database seed/export file.
- Complete testing records and evidence review.
- Final setup verification from a clean environment.
- Final documentation review and GitHub repository cleanup.

These items should be marked as complete only after implementation and testing.

## 9. Security Notice

This project intentionally contains vulnerable code paths for educational demonstrations.

- Run the application locally only.
- Do not deploy Vulnerable Mode to a public server.
- Use test accounts and non-sensitive data.
- Return to Safe Mode after completing demonstrations.
- The vulnerable implementations are not suitable for production use.

In a real application, security protections such as output encoding, prepared statements, and CSRF token validation must remain enabled at all times.

## 10. Project Status

**Current status:** Authentication, mode switching, three XSS demonstrations, and a mode-dependent CSRF demonstration have been implemented. SQL Injection demonstrations and final project packaging remain pending.

