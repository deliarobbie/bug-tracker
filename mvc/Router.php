<?php

//Contains the main routing logic for your framework.
require_once __DIR__ . '/utils/Session.php';

class Router {
    protected $routes = [];

    public function addRoute($route, $controller, $action) {
        $this->routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($uri) {

        // make sure users are logged in before routing
        $publicRoutes = ['/', 'login', 'login-submit'];

        if (!in_array($uri, $publicRoutes, true)) {
            $user = Session::getInstance()->get('user');

            if (!is_array($user) || empty($user['Id'])) {
                header('Location: ?login');
                exit;
            }
        }
        if (array_key_exists($uri, $this->routes)) {
            $controller = $this->routes[$uri]['controller'];
            $action = $this->routes[$uri]['action'];

            $controller = new $controller();
            $controller->$action();
        } else {
            //when done testing, replace with NotFound view
            //include("./views/NotFound.php");
            throw new \Exception("No route found for URI: $uri");
        }
    }
}