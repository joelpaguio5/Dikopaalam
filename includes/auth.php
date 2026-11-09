<?php
require_once __DIR__ . '/supabase.php';

function get_user_token() {
    return $_COOKIE['supabase_token'] ?? null;
}

function clear_user_token() {
    setcookie('supabase_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

/**
 * Ask Supabase who this token belongs to. Supabase verifies the signature,
 * so a forged or expired token returns null.
 */
function sb_get_user($token) {
    $config = get_supabase_config();
    $ch = curl_init(rtrim($config['url'], '/') . '/auth/v1/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $config['anon_key'],
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $user = json_decode($response, true);
        return (is_array($user) && isset($user['id'])) ? $user : null;
    }
    return null;
}

function get_user_profile($user_id, $token) {
    // sb_request() returns the decoded rows on success, or an array with 'http_code' on failure.
    $response = sb_request('GET', 'profiles', 'id=eq.' . urlencode($user_id) . '&select=*', null, $token);
    if (is_array($response) && isset($response[0]['id'])) {
        return $response[0];
    }
    return null;
}

/** Where each role lands after login (routes live in vercel.json). */
function role_home($role) {
    switch ($role) {
        case 'admin':     return '/admin/';
        case 'registrar': return '/registrar/';
        default:          return '/user/';
    }
}

/** Only checks that a cookie exists. Use require_role() for real protection. */
function require_login() {
    $token = get_user_token();
    if (!$token) {
        header('Location: /login.php');
        exit;
    }
    return $token;
}

/**
 * Protect a page by role, e.g. require_role(['admin']) or require_role(['registrar', 'admin']).
 * Returns ['token', 'user', 'profile', 'role'] for the page to use.
 */
function require_role(array $allowedRoles) {
    $token = require_login();

    $user = sb_get_user($token);
    if (!$user) {
        // Token expired or invalid
        clear_user_token();
        header('Location: /login.php');
        exit;
    }

    $profile = get_user_profile($user['id'], $token);
    $role = $profile['role'] ?? 'student';

    if (!in_array($role, $allowedRoles, true)) {
        header('Location: ' . role_home($role));
        exit;
    }

    return [
        'token'   => $token,
        'user'    => $user,
        'profile' => $profile,
        'role'    => $role
    ];
}