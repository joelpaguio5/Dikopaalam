<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';

// Which tab is selected: Student (default) or Admin. The landing page links here with ?as=admin.
$loginAs = ($_POST['login_as'] ?? $_GET['as'] ?? 'student') === 'admin' ? 'admin' : 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $authResult = sb_login($email, $password);

        if (isset($authResult['access_token'])) {
            $token = $authResult['access_token'];
            $userId = $authResult['user']['id'];

            $profile = get_user_profile($userId, $token);
            $role = $profile['role'] ?? 'student';

            // The tab is a convenience; the real role always comes from the database.
            if ($loginAs === 'admin' && $role !== 'admin') {
                $error = 'This is not an admin account. Choose Student to log in.';
            } elseif ($loginAs === 'student' && $role === 'admin') {
                $error = 'This is an admin account. Choose Admin to log in.';
            } else {
                // Cookie lasts as long as the access token itself (Supabase default: 1 hour)
                $lifetime = (int)($authResult['expires_in'] ?? 3600);

                setcookie('supabase_token', $token, [
                    'expires' => time() + $lifetime,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                header('Location: ' . role_home($role));
                exit;
            }
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
    <title>Login - CCST Portal</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="brand">CCST <span class="brand-ph">Portal</span></div>
        <h2>Welcome back</h2>
        <p class="auth-subtitle">Clark College of Science and Technology</p>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="/login.php">
            <p class="role-toggle-label">Login as</p>
            <div class="role-toggle" role="radiogroup" aria-label="Login as">
                <input type="radio" id="as-student" name="login_as" value="student" <?php echo $loginAs === 'student' ? 'checked' : ''; ?>>
                <label for="as-student">Student</label>
                <input type="radio" id="as-admin" name="login_as" value="admin" <?php echo $loginAs === 'admin' ? 'checked' : ''; ?>>
                <label for="as-admin">Admin</label>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
            <a href="/register.php" class="btn-secondary">New student? Create an account</a>
        </form>

        <p class="auth-footer-text"><a href="/" class="auth-link">&larr; Back to home</a></p>
    </div>
</body>
</html>