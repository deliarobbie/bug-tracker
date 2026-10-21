<?php
//This model will represent a user in your application.
// retrieves, creates, updates projects for users 
require_once(__DIR__.'/DB.php');

class User extends DB {
    public $name;
    public $email;
    public function __construct($name = '', $email = '') {
        parent::__construct();
        $this->name = $name;
        $this->email = $email;
    }

    public function findById($id) {
        // TODO: Retrieve an existing user using a prepared statement.
    }

    public function findByUsername($username) {
        // TODO: Retrieve the authentication record using the schema's actual login field.
    }

    public function findAll() {
        $data = [];

        try {
            $stmt = $this->dbh->prepare(
                "SELECT Id, Username, RoleID, ProjectId, Password, Name FROM user_details"
            );

            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;
    } // findAll

    public function create($data) {
        // TODO: Insert validated user data with a password hash using a prepared statement.
    }

    public function assignProject($userId, $projectId) {
        // TODO: Allow one project for a User, none for Admins/Managers.
    }

    public function deleteAndClearAssignments($id) {
        // TODO: Use a transaction to clear assignments and delete the user.
        // Preserve projects/bugs; resolve reporter references according to the supplied schema.
    }

    public function getRoles() {
        // TODO: Retrieve the existing roles using a prepared statement.
    }
}
