<?php

function load_env($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, '"\'');
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

load_env(__DIR__ . '/../.env');

function get_supabase_config() {
    // Prefer Vercel environment variables; fall back to the project values below.
    $url = getenv('SUPABASE_URL') ?: ($_ENV['SUPABASE_URL'] ?? ($_SERVER['SUPABASE_URL'] ?? 'https://bnjutokfsmbmeguaasqp.supabase.co'));
    $anon_key = getenv('SUPABASE_ANON_KEY') ?: ($_ENV['SUPABASE_ANON_KEY'] ?? ($_SERVER['SUPABASE_ANON_KEY'] ?? 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJuanV0b2tmc21ibWVndWFhc3FwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTM2NTg3MTEsImV4cCI6MjEwOTIzNDcxMX0.B3fymtFPb1HsOpID_G0tu5h9eOSCrXO1jD8qvM7cHIE'));

    return [
        'url' => is_string($url) ? trim($url) : '',
        'anon_key' => is_string($anon_key) ? trim($anon_key) : ''
    ];
}

/**
 * Sign in with email + password via Supabase Auth.
 * Success: returns the decoded session (access_token, user, ...).
 * Failure: returns ['error' => ..., 'http_code' => ..., 'response_body' => ...].
 */
function sb_login($email, $password) {
    $config = get_supabase_config();
    $endpoint = rtrim($config['url'], '/') . '/auth/v1/token?grant_type=password';

    $payload = json_encode([
        'email' => $email,
        'password' => $password
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $config['anon_key']
    ]);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    // Only what login.php needs; the config and endpoint are never returned.
    return [
        'error' => 'Authentication failed',
        'http_code' => $httpCode,
        'response_body' => json_decode($response, true) ?: []
    ];
}

/**
 * Register a new account. The handle_new_user() trigger in Supabase reads
 * full_name, phone and applicant_type from the metadata and creates the profile.
 * Role is never sent: the database decides it (default 'student').
 */
function sb_register($email, $password, $fullName = '', $applicantType = 'freshman', $phone = '') {
    $config = get_supabase_config();
    $endpoint = rtrim($config['url'], '/') . '/auth/v1/signup';

    if (!in_array($applicantType, ['freshman', 'transferee', 'returnee'], true)) {
        $applicantType = 'freshman';
    }

    // The REST API takes user metadata under a top-level "data" key.
    $payload = json_encode([
        'email' => $email,
        'password' => $password,
        'data' => [
            'full_name'      => $fullName,
            'phone'          => $phone,
            'applicant_type' => $applicantType
        ]
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $config['anon_key']
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    if ($httpCode === 200 || $httpCode === 201) {
        return $decoded;
    }

    // Supabase returns errors as {"msg": ...} or {"error_description": ...}.
    $message = 'Registration failed';
    if (is_array($decoded)) {
        $message = $decoded['msg'] ?? $decoded['error_description'] ?? $decoded['message'] ?? $message;
    }
    return ['error' => $message, 'http_code' => $httpCode];
}

/**
 * Make REST API requests to Supabase tables (RLS is enforced via the user's access token).
 * Success returns the decoded rows; failure returns an array with 'http_code'.
 */
function sb_request($method, $table, $query = '', $body = null, $userToken = null) {
    $config = get_supabase_config();
    $endpoint = rtrim($config['url'], '/') . '/rest/v1/' . $table;

    if (!empty($query)) {
        $endpoint .= '?' . $query;
    }

    $headers = [
        'apikey: ' . $config['anon_key'],
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    $headers[] = 'Authorization: Bearer ' . ($userToken ?: $config['anon_key']);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 201 = row created, which is what POST returns
    if ($httpCode === 200 || $httpCode === 201) {
        return json_decode($response, true);
    }

    return [
        'http_code' => $httpCode,
        'raw_response' => json_decode($response, true),
        'response_string' => $response
    ];
}