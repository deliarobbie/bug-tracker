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
        $sql = 'SELECT b.id AS Id, b.summary AS Summary, b.projectId AS ProjectId,
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
        $stmt = $this->dbh->prepare(
            'SELECT id AS Id, projectId AS ProjectId, assignedToId AS AssignedTo,
                    statusId AS StatusId, priorityId AS PriorityId, summary AS Summary,
                    description AS Description, fixDescription AS Fix,
                    ownerId AS OwnerId, dateRaised AS DateRaised,
                    targetDate AS TargetDate, dateClosed AS ClosureDate
             FROM bugs WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->dbh->prepare(
            "INSERT INTO bugs
                (projectId, ownerId, assignedToId, statusId, priorityId, summary,
                 description, fixDescription, dateRaised, targetDate, dateClosed)
             VALUES
                (:projectId, :ownerId, :assignedToId,
                 :statusId,
                 COALESCE(:priorityId, (SELECT Id FROM priority WHERE Priority = 'Medium')),
                 :summary, :description, NULL, CURRENT_TIMESTAMP, :targetDate, NULL)"
        );

        return $stmt->execute([
            'projectId' => $data['projectId'],
            'ownerId' => $data['ownerId'],
            'assignedToId' => ($data['assignedToId'] ?? null) ?: null,
            'statusId' => empty($data['assignedToId']) ? 1 : 2,
            'priorityId' => ($data['priorityId'] ?? null) ?: null,
            'summary' => $data['summary'] ?? '',
            'description' => $data['description'] ?? '',
            'targetDate' => ($data['targetDate'] ?? null) ?: null,
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->dbh->prepare(
            'UPDATE bugs SET assignedToId = :assignedToId, statusId = :statusId,
             priorityId = :priorityId, targetDate = :targetDate,
             fixDescription = :fixDescription, dateClosed = :dateClosed WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'assignedToId' => $data['assignedToId'],
            'statusId' => !empty($data['dateClosed']) ? 3 : (empty($data['assignedToId']) ? 1 : 2),
            'priorityId' => $data['priorityId'],
            'targetDate' => $data['targetDate'],
            'fixDescription' => $data['fixDescription'],
            'dateClosed' => $data['dateClosed'],
        ]);
    }

    public function referenceExists($table, $id)
    {
        if (!in_array($table, ['project', 'user_details', 'priority'], true)) {
            throw new InvalidArgumentException('Invalid reference table.');
        }
        $stmt = $this->dbh->prepare("SELECT Id FROM $table WHERE Id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() !== false;
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

 
