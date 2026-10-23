<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars(
        (string) ($title ?? 'Bug Tracker'),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    ) ?></title>

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
    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>