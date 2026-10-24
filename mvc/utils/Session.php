<?php

class Session {
    //Singleton pattern
    // Hold the class instance.
    private static $instance = null;

    //could take in a parameter for session name
    private function __construct() {
        session_name("bug-tracker-session");
        session_start();
    }

    //could take in a parameter for session name
    public static function getInstance() {
        if (self::$instance == null)
        {
            self::$instance = new Session();
        }

        return self::$instance;
    }

    function get($name) {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return "";
    }

    function set($name, $value) {
        if ($name !== "" && isset($value)) {
            $_SESSION[$name] = $value;
        }
    }

    function destroy() {
        session_unset();

        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(session_name(),"",1,$params['domain'],
                $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
