<?php

//entry point of the application - routes are determined by query variables
$uri = parse_url($_SERVER['REQUEST_URI']);
$uri = isset($uri['query']) ? $uri['query'] : "/";

require('./routes.php');

$router->dispatch($uri);