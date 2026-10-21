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
        try {
            $userModel = new User();
            $users = $userModel->findAll();

            $this->render('debugging/index', ['users' => $users]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo 'Unable to load users.';
        }
    }

    //create other functions if needed. Should match those set in
    //routes.php
    //for example purposes duplicating index but in reverse order
    public function list() {
        
        $users = [
            new User('Jane Doe', 'jane@example.com'),
            new User('John Doe', 'john@example.com')
        ];

        // TODO: Restrict this development-only sample to authorized Admins.
        $this->render('debugging/index', ['users' => $users]);
    }
}
