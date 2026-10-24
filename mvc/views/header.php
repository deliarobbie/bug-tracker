<?php
if (!isset($title)) {
    $title = 'Bug Tracker';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars(
        (string) $title,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    ) ?></title>

    <!-- i did use AI to help me with front end and styling -->
    <!-- chatgpt: 
     "style all of the views. 
     do not change any code or functionality of the program at all, 
     simply apply styles to make it look nice"
    -->
    Temporarily disabled external styles. Remove this comment wrapper to restore.
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
   

    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        main {
            flex: 1;
            width: 100%;
            padding-bottom: 30px;
        }

        .site-footer {
            padding: 20px;
            background: #222;
            color: #ccc;
            text-align: center;
        }

        .bug-details-page {
            max-width: 1000px;
            margin: 0 auto;
        }

        .bug-details-card {
            padding: 24px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            margin-bottom: 24px;
        }

        .bug-details-card h2 {
            margin-top: 0;
            margin-bottom: 24px;
        }

        .bug-details-list {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            margin-bottom: 0;
        }

        .bug-details-list dt,
        .bug-details-list dd {
            margin: 0;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .bug-details-list dt {
            padding-right: 20px;
            color: #555;
        }

        .bug-details-list dd {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .bug-details-list dd:empty::before {
            content: '\2014';
            color: #777;
        }

        .bug-update-form {
            margin-top: 28px;
            padding-top: 4px;
            border-top: 1px solid #ddd;
        }

        @media (max-width: 480px) {
            .bug-details-card { padding: 16px; }
            .bug-details-list { grid-template-columns: minmax(0, 1fr); }
            .bug-details-list dt { border-bottom: 0; padding-bottom: 4px; }
            .bug-details-list dd { padding-top: 0; }
        }
    </style>

</head>
<body>
