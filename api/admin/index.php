<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

date_default_timezone_set('Asia/Manila');

// Only admins can open this page. Students are sent to their own dashboard.
$ctx = require_role(['admin']);
$token = $ctx['token'];
$adminName = $ctx['profile']['full_name'] ?? 'Admin';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function to_manila($iso) {
    try {
        $d = new DateTime($iso);
        $d->setTimezone(new DateTimeZone('Asia/Manila'));
        return $d;
    } catch (Exception $ex) {
        return null;
    }
}

function format_date($iso) {
    if (empty($iso)) {
        return '-';
    }
    $d = to_manila($iso);
    return $d ? $d->format('M j, Y g:i A') : '-';
}

$typeLabels = [
    'freshman'   => 'Freshman',
    'transferee' => 'Transferee',
    'returnee'   => 'Returning'
];

// Load all student profiles (admins can read every row thanks to the database policy).
// Supabase returns up to 1000 rows per request, which is plenty for now.
$students = sb_request(
    'GET',
    'profiles',
    'select=id,full_name,email,phone,applicant_type,student_number,is_active,created_at&role=eq.student&order=created_at.desc&limit=1000',
    null,
    $token
);

$loadError = '';
if (!is_array($students) || isset($students['http_code'])) {
    $loadError = 'Could not load student records (error code ' . ($students['http_code'] ?? 'unknown') . ').';
    $students = [];
}

// Numbers for the stat cards
$stats = [
    'total'      => count($students),
    'new_month'  => 0,
    'freshman'   => 0,
    'transferee' => 0,
    'returnee'   => 0,
    'active'     => 0
];
$thisMonth = date('Y-m');

foreach ($students as $s) {
    $type = $s['applicant_type'] ?? '';
    if (in_array($type, ['freshman', 'transferee', 'returnee'], true)) {
        $stats[$type]++;
    }
    if (!empty($s['is_active'])) {
        $stats['active']++;
    }
    if (!empty($s['created_at'])) {
        $d = to_manila($s['created_at']);
        if ($d && $d->format('Y-m') === $thisMonth) {
            $stats['new_month']++;
        }
    }
}

$recent = array_slice($students, 0, 10);

// Admin modules. When you build a page for one, set its 'url' (for example '/admin/students.php')
// and its button turns on automatically.
$modules = [
    ['title' => 'Student Forms',     'url' => '/admin/forms.php', 'desc' => 'Search students and read their enrollment forms. Open several at once and print them on A4.'],
    ['title' => 'Applications',      'url' => null, 'desc' => 'Review submitted enrollment applications and their documents, then approve or reject them.'],
    ['title' => 'Students',          'url' => null, 'desc' => 'View student accounts, update their details, and activate or deactivate accounts.'],
    ['title' => 'Student Numbers',   'url' => null, 'desc' => 'Assign student numbers to students whose enrollment has been approved.'],
    ['title' => 'Courses & Subjects','url' => null, 'desc' => 'Set up the courses, subjects, sections and prerequisites offered each semester.'],
    ['title' => 'Enrollment Period', 'url' => null, 'desc' => 'Open and close enrollment, and set the deadlines for each semester.'],
    ['title' => 'Requirements',      'url' => null, 'desc' => 'Choose which documents students must submit, such as an ID photo and school records.'],
    ['title' => 'Reports',           'url' => null, 'desc' => 'See enrollment numbers by course, student type and date.'],
    ['title' => 'Announcements',     'url' => null, 'desc' => 'Post notices for students, such as deadlines and schedule changes.']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CCST Portal</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-forms.css">
    <link rel="stylesheet" href="/css/skeleton.css">
    <script src="/js/ui.js"></script>
</head>
<body>
    <div class="admin-shell">

        <?php render_admin_sidebar('dashboard', $adminName); ?>

        <div class="workspace">

            <div class="workspace-bar">
                <h2 class="workspace-title">Dashboard</h2>
                <p class="admin-sub">Clark College of Science and Technology</p>
            </div>

        <main>
            <?php if ($loadError !== ''): ?>
                <div class="alert error"><?php echo e($loadError); ?></div>
            <?php endif; ?>

            <!-- OVERVIEW -->
            <section class="section">
                <h3 class="section-title">Overview</h3>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Registered students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['new_month']; ?></div>
                        <div class="stat-label">New this month</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['freshman']; ?></div>
                        <div class="stat-label">Freshmen</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['transferee']; ?></div>
                        <div class="stat-label">Transferees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['returnee']; ?></div>
                        <div class="stat-label">Returning students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['active']; ?></div>
                        <div class="stat-label">Active accounts</div>
                    </div>
                </div>
            </section>

            <!-- MANAGE -->
            <section class="section">
                <h3 class="section-title">Manage</h3>
                <div class="grid-cards">
                    <?php foreach ($modules as $m): ?>
                        <article class="card module-card">
                            <div class="module-card-head">
                                <h3><?php echo e($m['title']); ?></h3>
                                <?php if ($m['url'] === null): ?>
                                    <span class="badge badge-soon">Coming soon</span>
                                <?php endif; ?>
                            </div>
                            <p><?php echo e($m['desc']); ?></p>
                            <?php if ($m['url'] !== null): ?>
                                <a href="<?php echo e($m['url']); ?>" class="btn-module">Open</a>
                            <?php else: ?>
                                <button type="button" class="btn-module" disabled>Open</button>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- RECENT REGISTRATIONS -->
            <section class="section">
                <h3 class="section-title">Recent registrations</h3>
                <div class="table-wrap">
                    <?php if (count($recent) === 0): ?>
                        <p class="empty-state">No students have registered yet.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Type</th>
                                    <th>Student No.</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent as $s): ?>
                                    <tr>
                                        <td><?php echo e($s['full_name'] ?: '-'); ?></td>
                                        <td><?php echo e($s['email'] ?? '-'); ?></td>
                                        <td><?php echo e($s['phone'] ?: '-'); ?></td>
                                        <td><?php echo e($typeLabels[$s['applicant_type'] ?? ''] ?? '-'); ?></td>
                                        <td>
                                            <?php if (!empty($s['student_number'])): ?>
                                                <?php echo e($s['student_number']); ?>
                                            <?php else: ?>
                                                <span class="muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['is_active'])): ?>
                                                <span class="badge badge-active">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(format_date($s['created_at'] ?? '')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        </div>
    </div>
</body>
</html>