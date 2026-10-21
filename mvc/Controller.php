<?php

require_once(__DIR__.'/utils/Session.php');

//The base controller class that all other controllers will extend
class Controller {

    protected $session; //created here so can be used by all controllers

    //could take in a parameter for a session name
    function __construct() {
        $this->session = Session::getInstance();
    }

    protected function render($view, $data = []) {
        extract($data);
        
        include("./views/$view.php");
    }
    protected function redirect($route) {
        // TODO: Redirect to an internal application route and end processing.
    }
}   
