<?php

//This is just a dummy controller

//__DIR__ is needed here because this script is included from 
//another script in another folder so it uses that scripts location 
//instead of where this script is located for determining relative paths

require_once(__DIR__.'/../utils/Utils.php');
require_once(__DIR__.'/../Controller.php');

class DummyController extends Controller {
    public function index() {

        $this->render('dummy/index');
    }

    public function test() {

        //redirect example
        header("Location: index.php?user");
    }
}