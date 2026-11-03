<?php
require_once __DIR__ . '/../includes/supabase.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $role     = 'user'; // never taken from the form; admins are promoted manually in Supabase
    $userType = trim($_POST['user_type'] ?? 'client');
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Register user via Supabase Auth with metadata
        $result = sb_register($email, $password, $fullName, $role, $userType, $phone);

        if (isset($result['error'])) {
            $error = is_array($result['error']) ? ($result['error']['msg'] ?? 'Registration failed') : $result['error'];
        } else {
            $success = 'Registration successful! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basta ma deplyo muna - Register</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>Create Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="/register.php" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="+1234567890" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" required>
                    <option value="client" <?php echo (($_POST['user_type'] ?? '') === 'client') ? 'selected' : ''; ?>>Client</option>
                    <option value="caregiver" <?php echo (($_POST['user_type'] ?? '') === 'caregiver') ? 'selected' : ''; ?>>Caregiver</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Register</button>
        </form>

        <p class="auth-footer-text">
            Already have an account? <a href="/login.php" class="auth-link">Login here</a>
        </p>
    </div>
</body>
</html>