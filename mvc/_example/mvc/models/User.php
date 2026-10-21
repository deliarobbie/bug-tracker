<?php
//This model will represent a user in your application.

require(__DIR__.'/DB.php');

class User extends DB {
    public $name;
    public $email;

    public function __construct($name, $email) {
        parent::__construct();
        $this->name = $name;
        $this->email = $email;
    }
}