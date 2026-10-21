<?php

//This controller will handle user-related actions.

//__DIR__ is needed here because this script is included from 
//another script in another folder so it uses that scripts location 
//instead of where this script is located for determining relative paths

require_once(__DIR__.'/../utils/Utils.php');
require_once(__DIR__.'/../Controller.php');
require_once(__DIR__.'/../models/User.php');
//var_dump(__DIR__.'/../models/User.php');die();

class UserController extends Controller {
    public function index() {
        
        //Here is where you would use the database class(es)
        //to get your users - hard coding for now

        //also check session values if needed to see if authorized
        //or logged in.
        //e.g. $this->session->get("loggedIn");
        //e.g. $this->session->set("loggedIn",true);
        //e.g. $this->session->destroy();

        //here you would also user $_POST/$_GET as required
  
        $users = [
            new User('John Doe', 'john@example.com'),
            new User('Jane Doe', 'jane@example.com')
        ];

        $this->render('user/index', ['users' => $users]);
    }

    //create other functions if needed. Should match those set in
    //routes.php
    //for example purposes duplicating index but in reverse order
    public function list() {
        
        $users = [
            new User('Jane Doe', 'jane@example.com'),
            new User('John Doe', 'john@example.com')
        ];

        $this->render('user/index', ['users' => $users]);
    }
}