<?php

//Define the routes for your application.
require_once('./Router.php');
require_once('./controllers/AuthController.php');
require_once('./controllers/AdminController.php');
require_once('./controllers/BugController.php');

$router = new Router();

//Add routes here: "path", "Controller", "controllerFunction")
//default route for system - could be login or something else
$router->addRoute('/', 'AuthController', 'loginForm');
$router->addRoute('login', 'AuthController', 'loginForm');
$router->addRoute('login-submit', 'AuthController', 'login');
$router->addRoute('logout', 'AuthController', 'logout');
$router->addRoute('admin', 'AdminController', 'index');
$router->addRoute('user-create', 'AdminController', 'createUser');
$router->addRoute('user-delete', 'AdminController', 'deleteUser');
$router->addRoute('user-project', 'AdminController', 'assignProject');
$router->addRoute('project-create', 'AdminController', 'createProject');
$router->addRoute('project-update', 'AdminController', 'updateProject');
$router->addRoute('bugs', 'BugController', 'index');
$router->addRoute('bug-details', 'BugController', 'details');
$router->addRoute('bug-create', 'BugController', 'create');
$router->addRoute('bug-update', 'BugController', 'update');
// TODO: Require POST and CSRF checks for actions that change data.
