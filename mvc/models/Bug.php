<?php

require_once __DIR__ . '/DB.php';
// considers user role, filtering, owners, assignments, statuses, etc.  b
class Bug extends DB
{
    public function findAllVisibleToUser($user, $filters = [])
    {
        $roleId = (int) ($user['RoleID'] ?? 0);
        if (!in_array($roleId, [1, 2, 3], true)) {
            return [];
        }

        $where = [];
        $params = [];
        $projectId = $roleId === 3
            ? ($user['ProjectId'] ?? null) : ($filters['project_id'] ?? null);
        if ($roleId === 3 || ($projectId !== null && $projectId !== '')) {
            $projectId = filter_var($projectId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($projectId === false) {
                return [];
            }
            $where[] = 'b.projectId = :projectId';
            $params['projectId'] = $projectId;
        }

        $filter = $filters['filter'] ?? 'all';
        if (in_array($filter, ['open', 'overdue'], true)) {
            $where[] = 'b.dateClosed IS NULL';
        }
        if ($filter === 'overdue') {
            $where[] = 'b.targetDate < CURRENT_TIMESTAMP';
        }

        $sortColumns = [
            'id' => 'b.id',
            'project_id' => 'b.projectId',
            'status_id' => 'b.statusId',
            'priority_id' => 'b.priorityId',
            'target_date' => 'b.targetDate',
            'date_raised' => 'b.dateRaised',
        ];
        $sort = $filters['sort'] ?? 'id';
        $sortColumn = is_string($sort) ? ($sortColumns[$sort] ?? 'b.id') : 'b.id';
        $direction = strtoupper((string) ($filters['direction'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $sql = 'SELECT b.id AS Id, b.projectId AS ProjectId,
                       b.statusId AS StatusId, b.priorityId AS PriorityId,
                       b.assignedToId AS AssignedTo, b.targetDate AS TargetDate,
                       s.Status, p.Priority, u.Name AS AssignedToName
                FROM bugs b
                LEFT JOIN bug_status s ON s.Id = b.statusId
                LEFT JOIN priority p ON p.Id = b.priorityId
                LEFT JOIN user_details u ON u.Id = b.assignedToId';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY ' . $sortColumn . ' ' . $direction;

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            'summary' => $data['summary'] ?? '',
            'description' => $data['description'] ?? '',
            'targetDate' => ($data['targetDate'] ?? null) ?: null,
        ]);
    }

    public function update($id, $data)
    {
        // TODO: Save authorized validated changes.
    }

    public function getStatuses() {
        $data = [];

        try {
            $stmt = $this->dbh->prepare(
                "SELECT Id, Status FROM bug_status"
            );

            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;
    } // findAll

    public function getPriorities() {
        $data = [];

        try {
            $stmt = $this->dbh->prepare(
                "SELECT Id, Priority FROM priority"
            );

            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;
    } // findAll
}

 
