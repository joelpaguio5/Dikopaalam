<?php
/**
 * The questions on the student enrollment form.
 *
 * Used by BOTH the student form (api/user/forms/enrollment.php) and the admin
 * viewer (api/admin/forms.php), so they always match. Edit this one file to add,
 * remove or rename a question.
 *
 * span = width on the paper (2 = one third, 3 = half, 4 = two thirds, 6 = full)
 */
function enrollment_sections() {
    return [
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
}