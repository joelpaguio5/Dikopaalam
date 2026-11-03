<?php
require_once __DIR__ . '/supabase.php';


function get_user_token() {
    return $_COOKIE['supabase_token'] ?? null;
}


function require_login() {
    $token = get_user_token();
    if (!$token) {
        header('Location: /login.php');
        exit;
    }
    return $token;
}


function get_user_profile($user_id, $token) {
    // sb_request() returns the decoded rows directly on success (a list),
    // or an array with 'http_code' on failure. It never returns 'status'/'data'.
    $response = sb_request('GET', 'profiles', 'id=eq.' . urlencode($user_id) . '&select=*', null, $token);
    if (is_array($response) && isset($response[0]['id'])) {
        return $response[0];
    }
    return null;
}