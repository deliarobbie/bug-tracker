<?php

require_once __DIR__ . '/DB.php';

// Submission 1: intentional function shells. Database methods will use prepared statements.
class Project extends DB
{
    public function findAll()
    {
        // TODO: Query existing projects.
    }

    public function findById($id)
    {
        // TODO: Query one project.
    }

    public function findForUser($userId)
    {
        // TODO: Query the regular User's single assigned project.
    }

    public function create($data)
    {
        // TODO: Insert validated project fields.
    }

    public function update($id, $data)
    {
        // TODO: Update validated project fields.
    }

}

