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

$typeLabels = [
    'freshman'   => 'Freshman',
    'transferee' => 'Transferee',
    'returnee'   => 'Returning student'
];

// ------------------------------------------------------------------
// The form. Edit this list to add, remove or rename questions.
// span = width on the paper (2 = one third, 3 = half, 4 = two thirds, 6 = full)
// ------------------------------------------------------------------
$sections = [
    [
        'title' => 'Enrollment Details',
        'show_student_type' => true,
        'fields' => [
            ['name' => 'school_year', 'label' => 'School Year',      'type' => 'text',   'span' => 2, 'required' => true, 'placeholder' => '2026-2027'],
            ['name' => 'semester',    'label' => 'Semester',         'type' => 'select', 'span' => 2, 'required' => true, 'options' => ['1st Semester', '2nd Semester', 'Summer']],
            ['name' => 'year_level',  'label' => 'Year Level',       'type' => 'select', 'span' => 2, 'required' => true, 'options' => ['1st Year', '2nd Year', '3rd Year', '4th Year']],
            ['name' => 'course',      'label' => 'Course / Program', 'type' => 'text',   'span' => 4, 'required' => true],
        ]
    ],
    [
        'title' => 'Personal Information',
        'fields' => [
            ['name' => 'last_name',    'label' => 'Last Name',   'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'first_name',   'label' => 'First Name',  'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'middle_name',  'label' => 'Middle Name', 'type' => 'text', 'span' => 2],
            ['name' => 'birth_date',   'label' => 'Date of Birth',  'type' => 'date', 'span' => 2, 'required' => true],
            ['name' => 'birth_place',  'label' => 'Place of Birth', 'type' => 'text', 'span' => 4, 'required' => true],
            ['name' => 'sex',          'label' => 'Sex',            'type' => 'select', 'span' => 2, 'required' => true, 'options' => ['Male', 'Female']],
            ['name' => 'civil_status', 'label' => 'Civil Status',   'type' => 'select', 'span' => 2, 'required' => true, 'options' => ['Single', 'Married', 'Widowed', 'Separated']],
            ['name' => 'citizenship',  'label' => 'Citizenship',    'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'religion',     'label' => 'Religion',       'type' => 'text', 'span' => 3],
            ['name' => 'lrn',          'label' => 'LRN (Learner Reference No.)', 'type' => 'text', 'span' => 3],
        ]
    ],
    [
        'title' => 'Contact and Address',
        'fields' => [
            ['name' => 'mobile',  'label' => 'Mobile Number',  'type' => 'tel',   'span' => 3, 'required' => true],
            ['name' => 'email',   'label' => 'Email Address',  'type' => 'email', 'span' => 3, 'required' => true],
            ['name' => 'street',  'label' => 'House No., Street, Subdivision', 'type' => 'text', 'span' => 4, 'required' => true],
            ['name' => 'zip',     'label' => 'ZIP Code',       'type' => 'text', 'span' => 2],
            ['name' => 'barangay','label' => 'Barangay',       'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'city',    'label' => 'City / Municipality', 'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'province','label' => 'Province',       'type' => 'text', 'span' => 2, 'required' => true],
        ]
    ],
    [
        'title' => 'Family Background',
        'fields' => [
            ['name' => 'father_name',       'label' => "Father's Name",       'type' => 'text', 'span' => 2],
            ['name' => 'father_occupation', 'label' => 'Occupation',          'type' => 'text', 'span' => 2],
            ['name' => 'father_contact',    'label' => 'Contact Number',      'type' => 'tel',  'span' => 2],
            ['name' => 'mother_name',       'label' => "Mother's Maiden Name",'type' => 'text', 'span' => 2],
            ['name' => 'mother_occupation', 'label' => 'Occupation',          'type' => 'text', 'span' => 2],
            ['name' => 'mother_contact',    'label' => 'Contact Number',      'type' => 'tel',  'span' => 2],
            ['name' => 'guardian_name',         'label' => 'Guardian / Emergency Contact', 'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'guardian_relationship', 'label' => 'Relationship',    'type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'guardian_contact',      'label' => 'Contact Number',  'type' => 'tel',  'span' => 2, 'required' => true],
        ]
    ],
    [
        'title' => 'Educational Background',
        'fields' => [
            ['name' => 'last_school',         'label' => 'Last School Attended',          'type' => 'text', 'span' => 4, 'required' => true],
            ['name' => 'last_school_year',    'label' => 'Year Graduated / Last Attended','type' => 'text', 'span' => 2, 'required' => true],
            ['name' => 'last_school_address', 'label' => 'School Address',                 'type' => 'text', 'span' => 6],
        ]
    ],
];

$allFields = [];
foreach ($sections as $section) {
    foreach ($section['fields'] as $f) {
        $allFields[$f['name']] = $f;
    }
}

function render_field(array $f, $value, $editable) {
    $span = (int)($f['span'] ?? 3);
    $id = 'f-' . $f['name'];
    $disabled = $editable ? '' : ' disabled';

    echo '<div class="f f-' . $span . '">';
    echo '<label for="' . e($id) . '">' . e($f['label']);
    if (!empty($f['required'])) {
        echo ' <span class="req">*</span>';
    }
    echo '</label>';

    if ($f['type'] === 'select') {
        echo '<select id="' . e($id) . '" name="' . e($f['name']) . '"' . $disabled . '>';
        echo '<option value=""></option>';
        foreach ($f['options'] as $opt) {
            $selected = ($value === $opt) ? ' selected' : '';
            echo '<option value="' . e($opt) . '"' . $selected . '>' . e($opt) . '</option>';
        }
        echo '</select>';
    } else {
        $extra = '';
        if ($f['type'] === 'date') {
            $extra .= ' max="' . date('Y-m-d') . '"';
        }
        if (!empty($f['placeholder'])) {
            $extra .= ' placeholder="' . e($f['placeholder']) . '"';
        }
        echo '<input type="' . e($f['type']) . '" id="' . e($id) . '" name="' . e($f['name']) . '" value="' . e($value) . '" maxlength="200"' . $extra . $disabled . '>';
    }

    echo '</div>';
}

// ------------------------------------------------------------------
// Load this student's saved form (if any)
// ------------------------------------------------------------------
$loadError = '';
$existing = null;

$res = sb_request('GET', 'enrollments', 'student_id=eq.' . urlencode($userId) . '&select=*', null, $token);
if (sb_failed($res)) {
    if (($res['http_code'] ?? 0) === 404) {
        $loadError = 'The enrollment table is not set up yet. Run enrollments.sql in the Supabase SQL Editor.';
    } else {
        $loadError = 'Could not load your enrollment form (error code ' . ($res['http_code'] ?? 'unknown') . ').';
    }
} elseif (count($res) > 0) {
    $existing = $res[0];
}

$status = $existing['status'] ?? 'draft';
$saved = (is_array($existing['data'] ?? null)) ? $existing['data'] : [];
$editable = ($loadError === '' && in_array($status, ['draft', 'rejected'], true));

// ------------------------------------------------------------------
// Handle Save draft / Submit
// ------------------------------------------------------------------
$formError = '';
$posted = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $editable) {
    $action = (($_POST['action'] ?? '') === 'submit') ? 'submit' : 'save';
    $clean = [];
    $keep = [];
    $missing = [];
    $invalid = [];

    foreach ($allFields as $name => $f) {
        $raw = (isset($_POST[$name]) && is_string($_POST[$name])) ? trim($_POST[$name]) : '';
        $raw = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw) ?? '';
        $raw = mb_substr($raw, 0, 200);
        $keep[$name] = $raw;
        $ok = true;

        if ($raw !== '') {
            if ($f['type'] === 'select') {
                $ok = in_array($raw, $f['options'], true);
            } elseif ($f['type'] === 'date') {
                $d = DateTime::createFromFormat('Y-m-d', $raw);
                $ok = ($d && $d->format('Y-m-d') === $raw && $raw <= date('Y-m-d'));
            } elseif ($f['type'] === 'email') {
                $ok = (bool)filter_var($raw, FILTER_VALIDATE_EMAIL);
            } elseif ($f['type'] === 'tel') {
                $ok = (bool)preg_match('/^[0-9+()\-\s]{7,20}$/', $raw);
            }
        }

        if (!$ok) {
            $invalid[] = $f['label'];
            $raw = '';
        }
        if ($action === 'submit' && !empty($f['required']) && $raw === '' && $ok) {
            $missing[] = $f['label'];
        }
        $clean[$name] = $raw;
    }

    if (!empty($invalid) || !empty($missing)) {
        $parts = [];
        if (!empty($invalid)) {
            $parts[] = 'Please check these fields: ' . implode(', ', array_unique($invalid)) . '.';
        }
        if (!empty($missing)) {
            $parts[] = 'Please complete the required fields (marked *): ' . implode(', ', array_unique($missing)) . '.';
        }
        $formError = implode(' ', $parts);
        $posted = $keep;
    } else {
        $body = [
            'status'       => ($action === 'submit') ? 'submitted' : 'draft',
            'data'         => $clean,
            'submitted_at' => ($action === 'submit') ? gmdate('c') : null
        ];

        if ($existing) {
            $result = sb_request('PATCH', 'enrollments', 'student_id=eq.' . urlencode($userId), $body, $token);
        } else {
            $body['student_id'] = $userId;
            $result = sb_request('POST', 'enrollments', '', $body, $token);
        }

        if (sb_failed($result) || count($result) === 0) {
            $formError = 'Could not save your form. Please try again.';
            $posted = $keep;
        } else {
            // Redirect so refreshing the page doesn't send the form twice
            header('Location: /user/?msg=' . ($action === 'submit' ? 'submitted' : 'saved'));
            exit;
        }
    }
}

// ------------------------------------------------------------------
// Values shown in the form
// ------------------------------------------------------------------
$defaults = [
    'email'       => $profile['email'] ?? ($ctx['user']['email'] ?? ''),
    'mobile'      => $profile['phone'] ?? '',
    'citizenship' => 'Filipino'
];

$source = $posted ?? $saved;
$values = [];
foreach ($allFields as $name => $f) {
    $v = (isset($source[$name]) && is_string($source[$name])) ? $source[$name] : '';
    $values[$name] = ($v !== '') ? $v : ($defaults[$name] ?? '');
}

$notice = '';
$msg = $_GET['msg'] ?? '';
if ($msg === 'saved') {
    $notice = 'Draft saved. You can come back and finish it anytime.';
} elseif ($msg === 'submitted') {
    $notice = 'Application submitted. You can print a copy below.';
}

$statusInfo = [
    'draft'     => ['Draft',     'badge-draft',     'Not submitted yet. Fill out the form, then press Submit application.'],
    'submitted' => ['Submitted', 'badge-submitted', 'Waiting for the school to review your application.'],
    'approved'  => ['Approved',  'badge-approved',  'Your enrollment has been approved.'],
    'rejected'  => ['Returned',  'badge-rejected',  'The school sent your form back. Please review it and submit again.']
];
$statusLabel = $statusInfo[$status][0] ?? 'Draft';
$statusClass = $statusInfo[$status][1] ?? 'badge-draft';
$statusText  = $statusInfo[$status][2] ?? '';

$studentType = $typeLabels[$profile['applicant_type'] ?? ''] ?? '';
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

        <header class="no-print">
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
            <?php if ($loadError !== ''): ?>
                <div class="alert error no-print"><?php echo e($loadError); ?></div>
            <?php endif; ?>
            <?php if ($formError !== ''): ?>
                <div class="alert error no-print"><?php echo e($formError); ?></div>
            <?php endif; ?>
            <?php if ($notice !== ''): ?>
                <div class="alert success no-print"><?php echo e($notice); ?></div>
            <?php endif; ?>

            <section class="status-strip no-print">
                <span class="status-label">Application status</span>
                <span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                <span class="status-text"><?php echo e($statusText); ?></span>
            </section>

            <form method="POST" action="/user/" id="enroll-form" novalidate>

                <div class="toolbar no-print">
                    <?php if ($editable): ?>
                        <button type="submit" name="action" value="save" class="btn-outline">Save draft</button>
                        <button type="submit" name="action" value="submit" class="btn-solid" data-confirm="Submit your application? You won't be able to edit it after you submit.">Submit application</button>
                    <?php endif; ?>
                    <button type="button" id="print-btn" class="btn-outline">Print form (A4)</button>
                </div>
                <p class="print-tip no-print">Tip: in the print window, choose paper size <strong>A4</strong> and turn off "Headers and footers".</p>

                <!-- THE PAPER: this is what gets printed on A4 -->
                <div class="paper">

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
                                    <?php render_field($f, $values[$f['name']], $editable); ?>
                                <?php endforeach; ?>

                                <?php if (!empty($section['show_student_type'])): ?>
                                    <div class="f f-2">
                                        <label>Student Type</label>
                                        <div class="static-value"><?php echo e($studentType); ?></div>
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
                                <div class="blank-field"><?php echo e($studentNumber); ?></div>
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
            </form>
        </main>
    </div>

    <script>
        document.getElementById('print-btn').addEventListener('click', function () {
            window.print();
        });

        var submitBtn = document.querySelector('[data-confirm]');
        if (submitBtn) {
            submitBtn.addEventListener('click', function (event) {
                if (!window.confirm(submitBtn.getAttribute('data-confirm'))) {
                    event.preventDefault();
                }
            });
        }
    </script>
</body>
</html>