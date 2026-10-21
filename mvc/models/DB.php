<?php
//This model will represent a user in your application.

class DB {
    protected $dbh;
    public function __construct() {
        try {
            $this->dbh = new PDO(
                "mysql:host={$_SERVER['DB_SERVER']};dbname={$_SERVER['DB']}",
                $_SERVER['DB_USER'],
                $_SERVER['DB_PASSWORD']
            );

            $this->dbh->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch (PDOException $pe) {
            echo $pe->getMessage();
            die("Bad Database");
        }
    }

    protected function getConnection() {
        return $this->dbh;
    }
}
