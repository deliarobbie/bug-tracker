<?php

class Session {
    //Singleton pattern
    // Hold the class instance.
    private static $instance = null;

    private function __construct() {
        // sets the session name and starts
        session_name("bug-tracker-session");
        session_start();
    }

    public static function getInstance() {
        // creates the session instance and returns it
        if (self::$instance == null)
        {
            self::$instance = new Session();
        }

        return self::$instance;
    }

    function get($name) {
        // gets a session value, or returns an empty string if not set
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return "";
    }

    function set($name, $value) {
        // stores a session value
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
