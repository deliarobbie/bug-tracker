<?php

//Contains the main routing logic for your framework.

class Router {
    protected $routes = [];

    public function addRoute($route, $controller, $action) {
        $this->routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($uri) {

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