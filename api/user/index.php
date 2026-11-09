<?php
require_once __DIR__ . '/../../includes/auth.php';

date_default_timezone_set('Asia/Manila');

// Only students can open this page. Admins are sent to /admin/.
$ctx = require_role(['student']);
$token = $ctx['token'];
$userId = $ctx['user']['id'];
$profile = $ctx['profile'] ?? [];

$studentName = $profile['full_name'] ?? '';
if ($studentName === '') {
    $studentName = $ctx['user']['email'] ?? 'Student';
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// sb_request() returns rows on success, or an array with 'http_code' on failure
function sb_failed($res) {
    return !is_array($res) || isset($res['http_code']);
}

function format_date($iso) {
    if (empty($iso)) {
        return '-';
    }
    try {
        $d = new DateTime($iso);
        $d->setTimezone(new DateTimeZone('Asia/Manila'));
        return $d->format('M j, Y g:i A');
    } catch (Exception $ex) {
        return '-';
    }
}

$typeLabels = [
    'freshman'   => 'Freshman',
    'transferee' => 'Transferee',
    'returnee'   => 'Returning student'
];

// ------------------------------------------------------------------
// Where is this student's enrollment form?
// ------------------------------------------------------------------
$formStatus = 'none';
$updatedAt = '';
$setupNote = '';

$res = sb_request('GET', 'enrollments', 'student_id=eq.' . urlencode($userId) . '&select=status,updated_at', null, $token);
if (sb_failed($res)) {
    if (($res['http_code'] ?? 0) === 404) {
        $setupNote = 'The enrollment table is not set up yet. Run enrollments.sql in the Supabase SQL Editor.';
    } else {
        $setupNote = 'Could not check your enrollment form right now (error code ' . ($res['http_code'] ?? 'unknown') . ').';
    }
} elseif (count($res) > 0) {
    $formStatus = $res[0]['status'] ?? 'draft';
    $updatedAt = $res[0]['updated_at'] ?? '';
}

// label, badge class, button text, help text
$statusMap = [
    'none'      => ['Not started', 'badge-draft',     'Fill out form',       'Fill out your enrollment form, then submit it to the school.'],
    'draft'     => ['Draft',       'badge-draft',     'Continue form',       'You started your form. Finish it and press Submit application.'],
    'submitted' => ['Submitted',   'badge-submitted', 'View / print form',   'Your form was submitted. The school will review it. You can print a copy.'],
    'approved'  => ['Approved',    'badge-approved',  'View / print form',   'Your enrollment has been approved. You can print a copy for your records.'],
    'rejected'  => ['Returned',    'badge-rejected',  'Review and resubmit', 'The school sent your form back. Please review it and submit again.']
];
$info = $statusMap[$formStatus] ?? $statusMap['none'];
list($statusLabel, $statusClass, $buttonText, $helpText) = $info;

$studentType = $typeLabels[$profile['applicant_type'] ?? ''] ?? '-';
$studentNumber = $profile['student_number'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - CCST Portal</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/student.css">
</head>
<body>
    <div class="dashboard-container">

        <header>
            <div class="student-heading">
                <h2>Student Dashboard</h2>
                <p class="student-sub">CCST Portal &middot; Clark College of Science and Technology</p>
            </div>
            <div class="student-user">
                <span class="student-user-name"><?php echo e($studentName); ?></span>
                <span class="badge badge-role">Student</span>
                <a href="/logout.php" class="btn-logout">Logout</a>
            </div>
        </header>

        <main>
            <?php if ($setupNote !== ''): ?>
                <div class="alert error"><?php echo e($setupNote); ?></div>
            <?php endif; ?>

            <div class="welcome">
                <h3>Welcome, <?php echo e($studentName); ?></h3>
                <p>Complete your enrollment form here and follow your application.</p>
            </div>

            <!-- OVERVIEW -->
            <section class="section">
                <h3 class="section-title">Overview</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Application status</div>
                        <div class="info-value"><span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Student type</div>
                        <div class="info-value"><?php echo e($studentType); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Student number</div>
                        <div class="info-value">
                            <?php if ($studentNumber !== ''): ?>
                                <?php echo e($studentNumber); ?>
                            <?php else: ?>
                                <span class="muted">Not assigned yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Form last updated</div>
                        <div class="info-value"><?php echo e(format_date($updatedAt)); ?></div>
                    </div>
                </div>
            </section>

            <!-- FORMS AND SERVICES -->
            <section class="section">
                <h3 class="section-title">Forms and services</h3>
                <div class="grid-cards">
                    <article class="card module-card">
                        <div class="module-card-head">
                            <h3>Student Enrollment Form</h3>
                            <span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                        </div>
                        <p><?php echo e($helpText); ?></p>
                        <a href="/user/forms/enrollment.php" class="btn-module"><?php echo e($buttonText); ?></a>
                    </article>

                    <article class="card module-card">
                        <div class="module-card-head">
                            <h3>Upload Requirements</h3>
                            <span class="badge badge-soon">Coming soon</span>
                        </div>
                        <p>Upload the documents the school asks for, such as your ID photo and school records.</p>
                        <button type="button" class="btn-module" disabled>Open</button>
                    </article>

                    <article class="card module-card">
                        <div class="module-card-head">
                            <h3>Announcements</h3>
                            <span class="badge badge-soon">Coming soon</span>
                        </div>
                        <p>Read notices from the school, such as deadlines and schedule changes.</p>
                        <button type="button" class="btn-module" disabled>Open</button>
                    </article>
                </div>
            </section>

            <!-- MY DETAILS -->
            <section class="section">
                <h3 class="section-title">My details</h3>
                <div class="card">
                    <dl class="details-list">
                        <dt>Full name</dt>
                        <dd><?php echo e($profile['full_name'] ?? '-'); ?></dd>
                        <dt>Email</dt>
                        <dd><?php echo e($profile['email'] ?? ($ctx['user']['email'] ?? '-')); ?></dd>
                        <dt>Mobile</dt>
                        <dd><?php echo e(($profile['phone'] ?? '') !== '' ? $profile['phone'] : '-'); ?></dd>
                        <dt>Student type</dt>
                        <dd><?php echo e($studentType); ?></dd>
                    </dl>
                </div>
            </section>
        </main>

    </div>
</body>
</html>