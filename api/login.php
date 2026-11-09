<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $authResult = sb_login($email, $password);

        if (isset($authResult['access_token'])) {
            $token = $authResult['access_token'];
            $userId = $authResult['user']['id'];

            // Cookie lasts as long as the access token itself (Supabase default: 1 hour)
            $lifetime = (int)($authResult['expires_in'] ?? 3600);

            setcookie('supabase_token', $token, [
                'expires' => time() + $lifetime,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            $profile = get_user_profile($userId, $token);
            $role = $profile['role'] ?? 'student';

            header('Location: ' . role_home($role));
            exit;
        } else {
            $code = $authResult['response_body']['error_code'] ?? '';
            if ($code === 'email_not_confirmed') {
                $error = 'Please confirm your email first. Check your inbox for the confirmation link.';
            } elseif ($code === 'invalid_credentials') {
                $error = 'Invalid email or password.';
            } else {
                $error = 'Login failed. Please try again.';
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MatrikulaPH</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="brand">Matrikula<span class="brand-ph">PH</span></div>
        <h2>Sign in to your account</h2>
        <p class="auth-subtitle">Enroll, track your application, and manage your records.</p>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="/login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
            <a href="/register.php" class="btn-secondary">Create an account</a>
        </form>
    </div>
</body>
</html>