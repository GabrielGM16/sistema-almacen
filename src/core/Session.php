<?php

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function get($key) {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function csrf() {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf($token) {
        self::start();
        return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function user() {
        self::start();
        return $_SESSION['auth_user'] ?? null;
    }

    public static function login($user) {
        self::start();
        $_SESSION['auth_user'] = $user;
    }

    public static function logout() {
        self::start();
        session_regenerate_id(true);
        unset($_SESSION['auth_user']);
    }
}

