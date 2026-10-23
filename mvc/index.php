<?php

//entry point of the application - routes are determined by query variables
$uri = parse_url($_SERVER['REQUEST_URI']);
$routeParameters = [];
parse_str($uri['query'] ?? '', $routeParameters);
$uri = array_key_first($routeParameters) ?? '/';

require('./routes.php');

$router->dispatch($uri);
