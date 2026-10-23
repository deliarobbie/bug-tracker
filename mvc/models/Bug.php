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
        $stmt = $this->dbh->prepare(
            "INSERT INTO bugs
                (projectId, ownerId, assignedToId, statusId, priorityId, summary,
                 description, fixDescription, dateRaised, targetDate, dateClosed)
             VALUES
                (:projectId, :ownerId, :assignedToId,
                 COALESCE(:statusId, (SELECT Id FROM bug_status WHERE Status = 'Unassigned')),
                 COALESCE(:priorityId, (SELECT Id FROM priority WHERE Priority = 'Medium')),
                 :summary, :description, NULL, CURRENT_TIMESTAMP, :targetDate, NULL)"
        );

        return $stmt->execute([
            'projectId' => $data['projectId'],
            'ownerId' => $data['ownerId'],
            'assignedToId' => ($data['assignedToId'] ?? null) ?: null,
            'statusId' => ($data['statusId'] ?? null) ?: null,
            'priorityId' => ($data['priorityId'] ?? null) ?: null,
            'summary' => $data['summary'],
            'description' => $data['description'],
            'targetDate' => ($data['targetDate'] ?? null) ?: null,
        ]);
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

 
