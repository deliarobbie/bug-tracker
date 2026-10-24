<?php

//entry point of the application - routes are determined by query variables
$uri = parse_url($_SERVER['REQUEST_URI']);
$routeParameters = [];
$query = '';
if (isset($uri['query'])) {
    $query = $uri['query'];
}
parse_str($query, $routeParameters);
$uri = array_key_first($routeParameters);
if ($uri === null) {
    $uri = '/';
}

require('./routes.php');

$router->dispatch($uri);
