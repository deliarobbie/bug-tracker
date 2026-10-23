<?php

require_once __DIR__ . '/DB.php';

// Submission 1: intentional function shells. Database methods will use prepared statements.
class Project extends DB
{
    public function findAll() {
        $data = [];

        try {
            $stmt = $this->dbh->prepare(
                "SELECT Id, Project FROM project"
            );

            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;
    } // findAll

    public function findById($id)
    {
        $stmt = $this->dbh->prepare(
            'SELECT Id, Project FROM project WHERE Id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findForUser($userId)
    {
        $stmt = $this->dbh->prepare(
            'SELECT p.Id, p.Project FROM project p INNER JOIN user_details u ON u.ProjectId = p.Id WHERE u.Id = :userId'
        );
        $stmt->execute(['userId' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->dbh->prepare(
            'INSERT INTO project (Project) VALUES (:Project)'
        );

        return $stmt->execute([
            'Project' => $data['Project'],
        ]);
    }

    public function update($id, $data)
    {
        // TODO: Update validated project fields.
    }

}

