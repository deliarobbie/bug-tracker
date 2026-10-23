<?php

require_once __DIR__ . '/../Controller.php';
// handles bug actions 
// bug list, details, creation, updates
// checks login permissions, validates input calls bug move_uploaded_file
// includes bug project retrieval from project model
// Submission 1: intentional function shells. Database methods will use prepared statements.
class BugController extends Controller
{
    public function index()
    {
        $this->render('bugs/index');
    }

    public function details()
    {
        // TODO: Check visibility and load bug details for the bug view.
    }

    public function create()
    {
        // TODO: Validate new bug; apply NULL assignee/target date, unassigned status and medium priority without assignment permission. Closure date and fix text start NULL.
    }

    public function update()
    {
        // TODO: Check access: Users update only assigned bugs; Admins/Managers all bugs. Validate permitted changes.
    }

}


