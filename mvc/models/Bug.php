<?php

require_once __DIR__ . '/DB.php';
// considers user role, filtering, owners, assignments, statuses, etc.  b
class Bug extends DB
{
    public function findVisibleToUser($user, $filters = [])
    {
        // TODO: Query permitted bugs; restrict Users by project. Apply all/open/overdue filters and allowlisted sorting in SQL.
    }

    public function findById($id)
    {
        // TODO: Query bug details; controller must check access.
    }

    public function create($data)
    {
        // TODO: Insert validated new bug fields with required defaults.
    }

    public function update($id, $data)
    {
        // TODO: Save authorized validated changes.
    }

    public function getStatuses()
    {
        // TODO: Query provided status IDs and labels.
    }

    public function getPriorities()
    {
        // TODO: Query provided priority IDs and labels.
    }

}

 