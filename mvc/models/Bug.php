<?php

require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/../utils/Authorization.php';
require_once __DIR__ . '/../utils/Utils.php';
// Retrieves and saves bugs using the database schema.
class Bug extends DB
{
    public function findAllVisibleToUser($user, $filters = [])
    {
        if (!Authorization::canAccessBugs($user)) {
            return [];
        }
        $canManage = Authorization::canAssignBugs($user);

        $where = [];
        $params = [];
        if (!$canManage) {
            if (isset($user['ProjectId'])) {
                $projectId = $user['ProjectId'];
            } else {
                $projectId = null;
            }
        } else {
            if (isset($filters['project_id'])) {
                $projectId = $filters['project_id'];
            } else {
                $projectId = null;
            }
        }
        if (!$canManage || ($projectId !== null && $projectId !== '')) {
            $projectId = Utils::parseId($projectId);
            if ($projectId === false) {
                return [];
            }
            $where[] = 'bugs.projectId = :projectId';
            $params['projectId'] = $projectId;
        }

        $filter = Utils::bugFilter(Utils::value($filters, 'filter', 'all'));
        if (in_array($filter, ['open', 'overdue'], true)) {
            $where[] = 'bugs.dateClosed IS NULL';
        }
        if ($filter === 'overdue') {
            $where[] = 'bugs.targetDate < CURRENT_TIMESTAMP';
        }

        $sortColumns = [
            'id' => 'bugs.id',
            'project_id' => 'bugs.projectId',
            'status_id' => 'bugs.statusId',
            'priority_id' => 'bugs.priorityId',
            'target_date' => 'bugs.targetDate',
            'date_raised' => 'bugs.dateRaised',
        ];
        $sort = Utils::bugSort(Utils::value($filters, 'sort'));
        $sortColumn = $sortColumns[$sort];
        $direction = Utils::sortDirection('ASC'); // oldest first
        $sql = 'SELECT bugs.id, bugs.summary, bugs.projectId,
                       bugs.statusId, bugs.priorityId,
                       bugs.assignedToId, bugs.targetDate,
                       bug_status.Status, priority.Priority, user_details.Name
                FROM bugs
                LEFT JOIN bug_status ON bug_status.Id = bugs.statusId
                LEFT JOIN priority ON priority.Id = bugs.priorityId
                LEFT JOIN user_details ON user_details.Id = bugs.assignedToId';
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
            'SELECT id, projectId, assignedToId,
                    statusId, priorityId, summary,
                    description, fixDescription,
                    ownerId, dateRaised,
                    targetDate, dateClosed
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
                 :priorityId,
                 :summary, :description, NULL, CURRENT_TIMESTAMP, :targetDate, NULL)"
        );

        $assignedToId = null;
        if (!empty($data['assignedToId'])) {
            $assignedToId = $data['assignedToId'];
        }
        $statusId = 2;
        if (empty($data['assignedToId'])) {
            $statusId = 1;
        }
        $priorityId = null;
        if (!empty($data['priorityId'])) {
            $priorityId = $data['priorityId'];
        }
        if ($priorityId === null) {
            // use medium priority when no priority was supplied
            $priorityStmt = $this->dbh->prepare(
                'SELECT Id FROM priority WHERE Priority = :priority'
            );
            // runs with 'Medium' and if not found throws exception
            $priorityStmt->execute(['priority' => 'Medium']);
            $priorityId = $priorityStmt->fetchColumn();

            if ($priorityId === false) {
                throw new PDOException('Medium priority was not found.');
            }
        }
        $summary = '';
        if (isset($data['summary'])) {
            $summary = $data['summary'];
        }
        $description = '';
        if (isset($data['description'])) {
            $description = $data['description'];
        }
        $targetDate = null;
        if (!empty($data['targetDate'])) {
            $targetDate = $data['targetDate'];
        }

        return $stmt->execute([
            'projectId' => $data['projectId'],
            'ownerId' => $data['ownerId'],
            'assignedToId' => $assignedToId,
            'statusId' => $statusId,
            'priorityId' => $priorityId,
            'summary' => $summary,
            'description' => $description,
            'targetDate' => $targetDate,
        ]);
    }

    public function update($id, $data)
    {
        $statusId = 1;
        if (!empty($data['dateClosed'])) {
            $statusId = 3;
        } elseif (!empty($data['assignedToId'])) {
            $statusId = 2;
        }

        $stmt = $this->dbh->prepare(
            'UPDATE bugs SET assignedToId = :assignedToId, statusId = :statusId,
             priorityId = :priorityId, targetDate = :targetDate,
             fixDescription = :fixDescription, dateClosed = :dateClosed WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'assignedToId' => $data['assignedToId'],
            'statusId' => $statusId,
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


