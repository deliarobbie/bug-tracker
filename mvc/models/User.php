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
        $stmt = $this->dbh->prepare(
            'SELECT Id, Username, RoleID, ProjectId, Password, Name FROM user_details WHERE Username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $stmt = $this->dbh->prepare(
            'INSERT INTO user_details (Username, RoleID, ProjectId, Password, Name) VALUES (:username, :role_id, :project_id, :password, :name)'
        );
        return $stmt->execute([
            'username' => $data['username'],
            'role_id' => $data['role_id'],
            'project_id' => $data['project_id'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'],
        ]);
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
