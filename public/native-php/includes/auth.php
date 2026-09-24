<?php

function hrdAuthCredentials()
{
    return [
        'username' => getenv('HRD_LOGIN_USERNAME') ?: 'admin',
        'password' => getenv('HRD_LOGIN_PASSWORD') ?: 'admin123',
    ];
}

function hrdIsAuthenticated()
{
    return !empty($_SESSION['hrd_authenticated']);
}

function hrdAttemptLogin($username, $password)
{
    $credentials = hrdAuthCredentials();

    if (hash_equals($credentials['username'], trim($username)) && password_verify($password, password_hash($credentials['password'], PASSWORD_DEFAULT))) {
        session_regenerate_id(true);
        $_SESSION['hrd_authenticated'] = true;
        $_SESSION['hrd_username'] = $credentials['username'];
        return true;
    }

    return false;
}

function hrdLogout()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
