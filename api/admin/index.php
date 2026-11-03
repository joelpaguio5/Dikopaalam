<?php
require_once __DIR__ . '/../../includes/auth.php';

// Protect page: ensures user is logged in
$token = require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ADMIN malakas Dashboard - Appnapamalakasan</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h2>Patient Dashboard</h2>
            <a href="/logout.php" class="btn-logout">Logout</a>
        </header>
        <main>
            <p>WELCOME KKUUPAALLLL HAHAHAHAH</p>
        </main>
    </div>
</body>
</html>