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
            'SELECT project.Id, project.Project FROM project INNER JOIN user_details ON user_details.ProjectId = project.Id WHERE user_details.Id = :userId'
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
        $stmt = $this->dbh->prepare(
            'UPDATE project SET Project = :Project WHERE Id = :id'
        );

        return $stmt->execute([
            'Project' => $data['Project'],
            'id' => $id,
        ]);
    }

}

