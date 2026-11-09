<?php
require_once __DIR__ . '/../includes/supabase.php';

$error = '';
$success = '';

$applicantTypes = [
    'freshman'   => 'Freshman (new student)',
    'transferee' => 'Transferee',
    'returnee'   => 'Returning student'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName      = trim($_POST['full_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $applicantType = trim($_POST['applicant_type'] ?? 'freshman');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!array_key_exists($applicantType, $applicantTypes)) {
        $error = 'Please choose a valid applicant type.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Role is never taken from the form. The database sets it to 'student'.
        $result = sb_register($email, $password, $fullName, $applicantType, $phone);

        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            $success = 'Registration successful! Check your email to confirm your account, then log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MatrikulaPH</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="brand">Matrikula<span class="brand-ph">PH</span></div>
        <h2>Create your account</h2>
        <p class="auth-subtitle">Start your enrollment application.</p>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="/register.php" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required autocomplete="name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Mobile Number</label>
                <input type="tel" id="phone" name="phone" required autocomplete="tel" placeholder="+63 9XX XXX XXXX" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="applicant_type">Applicant Type</label>
                <select id="applicant_type" name="applicant_type" required>
                    <?php foreach ($applicantTypes as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo (($_POST['applicant_type'] ?? 'freshman') === $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                <small class="form-hint">At least 8 characters.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-primary">Register</button>
        </form>

        <p class="auth-footer-text">
            Already have an account? <a href="/login.php" class="auth-link">Login here</a>
        </p>
    </div>
</body>
</html>