<?php

//Define the routes for your application.
require_once('./Router.php');
require_once('./controllers/UserController.php');
require_once('./controllers/DummyController.php');

$router = new Router();

//Add routes here: "path", "Controller", "controllerFunction")
//default route for system - could be login or something else
$router->addRoute('/','UserController', 'index'); 
$router->addRoute('user','UserController', 'list'); 
$router->addRoute('dummy','DummyController', 'index'); 
$router->addRoute('dummytest','DummyController', 'test'); 