<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/enrollment-fields.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

date_default_timezone_set('Asia/Manila');

// Only admins can open this page. Students are sent to their own dashboard.
$ctx = require_role(['admin']);
$token = $ctx['token'];
$adminName = $ctx['profile']['full_name'] ?? 'Admin';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// sb_request() returns rows on success, or an array with 'http_code' on failure
function sb_failed($res) {
    return !is_array($res) || isset($res['http_code']);
}

function is_uuid($value) {
    return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1;
}

function txt($value) {
    return is_string($value) ? trim($value) : '';
}

function format_dt($iso) {
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

function format_value(array $f, $value) {
    if ($value === '' || $value === null) {
        return '';
    }
    if (($f['type'] ?? '') === 'date') {
        $d = DateTime::createFromFormat('Y-m-d', (string)$value);
        if ($d && $d->format('Y-m-d') === $value) {
            return $d->format('F j, Y');
        }
    }
    return (string)$value;
}

// Read-only version of a form field (no input box, so it prints cleanly)
function render_static_field(array $f, $value) {
    $span = (int)($f['span'] ?? 3);
    echo '<div class="f f-' . $span . '">';
    echo '<label>' . e($f['label']) . '</label>';
    echo '<div class="static-value">' . e(format_value($f, $value)) . '</div>';
    echo '</div>';
}

// Builds a link to this page. The open tabs live in the URL, so they survive a refresh.
function forms_url(array $open, $active, $q, $status, $searched) {
    $params = [];
    if (count($open) > 0) {
        $params['open'] = implode(',', $open);
    }
    if ($active !== '' && $active !== null) {
        $params['active'] = $active;
    }
    if ($searched) {
        $params['s'] = '1';
        if ($q !== '') {
            $params['q'] = $q;
        }
        if ($status !== 'all') {
            $params['status'] = $status;
        }
    }
    return '/admin/forms.php' . (count($params) ? '?' . http_build_query($params) : '');
}

$statusMap = [
    'draft'     => ['Draft',     'badge-draft'],
    'submitted' => ['Submitted', 'badge-submitted'],
    'approved'  => ['Approved',  'badge-approved'],
    'rejected'  => ['Returned',  'badge-rejected']
];

$typeLabels = [
    'freshman'   => 'Freshman',
    'transferee' => 'Transferee',
    'returnee'   => 'Returning student'
];

$sections = enrollment_sections();

// ------------------------------------------------------------------
// Load every form and the matching student profiles.
// (Admins can read all rows thanks to the database policies. Supabase returns up to 1000 rows.)
// ------------------------------------------------------------------
$loadError = '';

$formRows = sb_request('GET', 'enrollments', 'select=student_id,status,submitted_at,updated_at,data&order=updated_at.desc&limit=1000', null, $token);
if (sb_failed($formRows)) {
    if (($formRows['http_code'] ?? 0) === 404) {
        $loadError = 'The enrollment table is not set up yet. Run enrollments.sql in the Supabase SQL Editor.';
    } else {
        $loadError = 'Could not load the student forms (error code ' . ($formRows['http_code'] ?? 'unknown') . ').';
    }
    $formRows = [];
}

$profileRows = sb_request('GET', 'profiles', 'select=id,full_name,email,phone,applicant_type,student_number&role=eq.student&limit=1000', null, $token);
if (sb_failed($profileRows)) {
    if ($loadError === '') {
        $loadError = 'Could not load the student list (error code ' . ($profileRows['http_code'] ?? 'unknown') . ').';
    }
    $profileRows = [];
}

$profiles = [];
foreach ($profileRows as $p) {
    if (isset($p['id'])) {
        $profiles[$p['id']] = $p;
    }
}

$records = [];
foreach ($formRows as $row) {
    $id = $row['student_id'] ?? '';
    if (!is_uuid($id)) {
        continue;
    }

    $p = $profiles[$id] ?? [];
    $data = is_array($row['data'] ?? null) ? $row['data'] : [];
    $st = $row['status'] ?? 'draft';
    if (!isset($statusMap[$st])) {
        $st = 'draft';
    }

    $last = txt($data['last_name'] ?? '');
    $first = txt($data['first_name'] ?? '');
    $middle = txt($data['middle_name'] ?? '');

    if ($last !== '' && $first !== '') {
        $name = $last . ', ' . $first;
        if ($middle !== '') {
            $name .= ' ' . mb_substr($middle, 0, 1) . '.';
        }
    } elseif (txt($p['full_name'] ?? '') !== '') {
        $name = txt($p['full_name']);
    } elseif (txt($p['email'] ?? '') !== '') {
        $name = txt($p['email']);
    } else {
        $name = 'Unnamed student';
    }

    $records[$id] = [
        'id'             => $id,
        'name'           => $name,
        'email'          => txt($p['email'] ?? ''),
        'phone'          => txt($p['phone'] ?? ''),
        'type'           => $typeLabels[$p['applicant_type'] ?? ''] ?? '',
        'student_number' => txt($p['student_number'] ?? ''),
        'status'         => $st,
        'submitted_at'   => $row['submitted_at'] ?? '',
        'updated_at'     => $row['updated_at'] ?? '',
        'data'           => $data,
        'course'         => txt($data['course'] ?? ''),
        'year_level'     => txt($data['year_level'] ?? ''),
        'school_year'    => txt($data['school_year'] ?? '')
    ];
}

// ------------------------------------------------------------------
// Search & Default Listing
// ------------------------------------------------------------------
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim(mb_substr($_GET['q'], 0, 100)) : '';
$statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : 'all';
if ($statusFilter !== 'all' && !isset($statusMap[$statusFilter])) {
    $statusFilter = 'all';
}
// Force $searched to true by default so all recent forms show immediately
$searched = true;

$results = [];
if ($searched) {
    $needle = mb_strtolower($q);
    foreach ($records as $r) {
        if ($statusFilter !== 'all' && $r['status'] !== $statusFilter) {
            continue;
        }
        if ($needle !== '') {
            $haystack = mb_strtolower(implode(' ', [
                $r['name'], $r['email'], $r['phone'], $r['course'],
                $r['student_number'], $r['school_year'], $r['year_level']
            ]));
            if (mb_strpos($haystack, $needle) === false) {
                continue;
            }
        }
        $results[] = $r;
    }
}
$resultTotal = count($results);
$results = array_slice($results, 0, 50);

// ------------------------------------------------------------------
// Open tabs
// ------------------------------------------------------------------
$open = [];
$openParam = isset($_GET['open']) && is_string($_GET['open']) ? $_GET['open'] : '';
foreach (explode(',', $openParam) as $id) {
    $id = trim($id);
    if (is_uuid($id) && isset($records[$id]) && !in_array($id, $open, true)) {
        $open[] = $id;
    }
}
$open = array_slice($open, 0, 20);

$active = isset($_GET['active']) && is_string($_GET['active']) ? $_GET['active'] : '';
if (!in_array($active, $open, true)) {
    $active = count($open) > 0 ? end($open) : '';
}

$tabs = [];
foreach ($open as $id) {
    $r = $records[$id];
    $remaining = array_values(array_diff($open, [$id]));

    if ($id === $active) {
        $pos = array_search($id, $open, true);
        $afterClose = $open[$pos + 1] ?? ($open[$pos - 1] ?? '');
    } else {
        $afterClose = $active;
    }

    $tabs[] = [
        'name'         => $r['name'],
        'status_label' => $statusMap[$r['status']][0],
        'status_class' => $statusMap[$r['status']][1],
        'url'          => forms_url($open, $id, $q, $statusFilter, $searched),
        'close_url'    => forms_url($remaining, $afterClose, $q, $statusFilter, $searched),
        'active'       => ($id === $active)
    ];
}
$closeAllUrl = forms_url([], '', $q, $statusFilter, $searched);

$current = ($active !== '') ? $records[$active] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Forms - CCST Portal Admin</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/student.css">
    <link rel="stylesheet" href="/css/admin-forms.css">
    <link rel="stylesheet" href="/css/skeleton.css">
    <script src="/js/ui.js"></script>
</head>
<body>
    <div class="admin-shell">

        <?php render_admin_sidebar('forms', $adminName, $tabs, $closeAllUrl); ?>

        <div class="workspace">

            <div class="workspace-bar no-print">
                <h2 class="workspace-title">Student Forms</h2>
                <form method="GET" action="/admin/forms.php" class="search-form" role="search">
                    <?php if (count($open) > 0): ?>
                        <input type="hidden" name="open" value="<?php echo e(implode(',', $open)); ?>">
                    <?php endif; ?>
                    <?php if ($active !== ''): ?>
                        <input type="hidden" name="active" value="<?php echo e($active); ?>">
                    <?php endif; ?>
                    <input type="hidden" name="s" value="1">

                    <input type="search" name="q" class="search-input" placeholder="Search by name, email, course or student number" value="<?php echo e($q); ?>" aria-label="Search student forms">
                    <select name="status" class="search-select" aria-label="Filter by status">
                        <option value="all"<?php echo $statusFilter === 'all' ? ' selected' : ''; ?>>All statuses</option>
                        <?php foreach ($statusMap as $key => $info): ?>
                            <option value="<?php echo e($key); ?>"<?php echo $statusFilter === $key ? ' selected' : ''; ?>><?php echo e($info[0]); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-solid">Search</button>
                </form>
            </div>

            <?php if ($loadError !== ''): ?>
                <div class="alert error no-print"><?php echo e($loadError); ?></div>
            <?php endif; ?>

            <?php if ($searched): ?>
                <section class="search-results no-print" aria-label="Search results">
                    <div class="results-head">
                        <span>
                            <?php echo $resultTotal; ?> form<?php echo $resultTotal === 1 ? '' : 's'; ?> found<?php echo $resultTotal > 50 ? ' (showing the first 50, narrow your search)' : ''; ?>
                        </span>
                        <a href="<?php echo e(forms_url($open, $active, '', 'all', false)); ?>" class="side-link">Hide results</a>
                    </div>

                    <?php if ($resultTotal === 0): ?>
                        <p class="empty-state">No forms match your search.</p>
                    <?php else: ?>
                        <ul class="result-list">
                            <?php foreach ($results as $r): ?>
                                <?php
                                    $isOpen = in_array($r['id'], $open, true);
                                    $newOpen = $isOpen ? $open : array_slice(array_merge($open, [$r['id']]), -20);
                                    $meta = array_filter([$r['course'], $r['year_level'], $r['email']]);
                                ?>
                                <li class="<?php echo $isOpen ? 'is-open' : ''; ?>">
                                    <a href="<?php echo e(forms_url($newOpen, $r['id'], $q, $statusFilter, true)); ?>" class="result-link">
                                        <span class="result-main">
                                            <span class="result-name"><?php echo e($r['name']); ?></span>
                                            <span class="result-meta"><?php echo e(implode(' · ', $meta)); ?></span>
                                        </span>
                                        <span class="badge <?php echo e($statusMap[$r['status']][1]); ?>"><?php echo e($statusMap[$r['status']][0]); ?></span>
                                        <span class="result-open"><?php echo $isOpen ? 'Open' : '+ Open'; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($current === null): ?>

                <div class="empty-workspace no-print">
                    <?php if (count($records) === 0 && $loadError === ''): ?>
                        <h3>No student forms yet</h3>
                        <p>Forms appear here once students start filling out their enrollment form.</p>
                    <?php else: ?>
                        <h3>No form open</h3>
                        <p>Use the search bar above and click a student. Each form you open is added as a tab on the left, so you can switch between several students.</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <div class="form-actions no-print">
                    <span class="badge <?php echo e($statusMap[$current['status']][1]); ?>"><?php echo e($statusMap[$current['status']][0]); ?></span>
                    <span class="status-text">
                        <?php if ($current['submitted_at'] !== ''): ?>
                            Submitted <?php echo e(format_dt($current['submitted_at'])); ?> &middot;
                        <?php endif; ?>
                        Last updated <?php echo e(format_dt($current['updated_at'])); ?>
                    </span>
                    <button type="button" id="print-btn" class="btn-outline">Print form (A4)</button>
                </div>

                <!-- THE PAPER (read-only, slightly smaller text than the student's copy) -->
                <div class="paper paper-compact">

                    <div class="paper-header">
                        <div>
                            <div class="paper-school">Clark College of Science and Technology</div>
                            <div class="paper-title">Student Enrollment Form</div>
                            <div class="paper-meta">Printed on <?php echo date('F j, Y'); ?></div>
                        </div>
                        <div class="photo-box">Attach<br>ID photo<br>here</div>
                    </div>

                    <?php foreach ($sections as $section): ?>
                        <section class="form-section">
                            <h3 class="form-section-title"><?php echo e($section['title']); ?></h3>
                            <div class="form-grid">
                                <?php foreach ($section['fields'] as $f): ?>
                                    <?php render_static_field($f, txt($current['data'][$f['name']] ?? '')); ?>
                                <?php endforeach; ?>

                                <?php if (!empty($section['show_student_type'])): ?>
                                    <div class="f f-2">
                                        <label>Student Type</label>
                                        <div class="static-value"><?php echo e($current['type']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>

                    <section class="form-section signature-block">
                        <h3 class="form-section-title">Certification</h3>
                        <p class="certification">
                            I certify that the information given in this form is true and correct to the best of
                            my knowledge. I understand that any false information may be a ground for the
                            cancellation of my enrollment.
                        </p>
                        <div class="signature-grid">
                            <div class="signature-line">Student's signature over printed name</div>
                            <div class="signature-line">Date</div>
                            <div class="signature-line">Parent / Guardian signature</div>
                        </div>
                    </section>

                    <section class="registrar-box">
                        <h3 class="registrar-title">For Registrar's Use Only</h3>
                        <div class="registrar-grid">
                            <div>
                                <span class="registrar-label">Student No.</span>
                                <div class="blank-field"><?php echo e($current['student_number']); ?></div>
                            </div>
                            <div>
                                <span class="registrar-label">Requirements checked by</span>
                                <div class="blank-field"></div>
                            </div>
                            <div>
                                <span class="registrar-label">Approved by (Registrar)</span>
                                <div class="blank-field"></div>
                            </div>
                            <div>
                                <span class="registrar-label">Date</span>
                                <div class="blank-field"></div>
                            </div>
                            <div class="registrar-wide">
                                <span class="registrar-label">Remarks</span>
                                <div class="blank-field"></div>
                            </div>
                        </div>
                    </section>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <script>
        var printBtn = document.getElementById('print-btn');
        if (printBtn) {
            printBtn.addEventListener('click', function () {
                window.print();
            });
        }
    </script>
</body>
</html>