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
        $projectId = null;
        if (isset($data['project_id'])) {
            $projectId = $data['project_id'];
        }
        return $stmt->execute([
            'username' => $data['username'],
            'role_id' => $data['role_id'],
            'project_id' => $projectId,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'],
        ]);
    }

    public function assignProject($userId, $projectId) {
        // TODO: Allow one project for a User, none for Admins/Managers.
    }

    public function deleteAndClearAssignments($id) {
        $this->dbh->beginTransaction();

        try {
            $stmt = $this->dbh->prepare(
                'SELECT Id FROM user_details WHERE Id <> :id ORDER BY RoleID, Id LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['id' => $id]);
            $replacementId = $stmt->fetchColumn();

            if ($replacementId === false) {
                $stmt = $this->dbh->prepare('SELECT id FROM bugs WHERE ownerId = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                if ($stmt->fetchColumn() !== false) {
                    throw new PDOException('Cannot delete the last user while they own bugs.');
                }
            } else {
                $stmt = $this->dbh->prepare(
                    'UPDATE bugs SET ownerId = :replacement_id WHERE ownerId = :id'
                );
                $stmt->execute(['replacement_id' => $replacementId, 'id' => $id]);
            }

            $stmt = $this->dbh->prepare('UPDATE bugs SET assignedToId = NULL WHERE assignedToId = :id');
            $stmt->execute(['id' => $id]);

            $stmt = $this->dbh->prepare('DELETE FROM user_details WHERE Id = :id');
            $stmt->execute(['id' => $id]);
            $deleted = $stmt->rowCount() > 0;

            $this->dbh->commit();
            return $deleted;
        } catch (Throwable $e) {
            if ($this->dbh->inTransaction()) {
                $this->dbh->rollBack();
            }
            throw $e;
        }
    }

    public function getRoles() {
        $stmt = $this->dbh->prepare('SELECT Id, Role FROM role ORDER BY Id');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
