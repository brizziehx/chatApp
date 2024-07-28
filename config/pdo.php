<?php
    // DATABASE CONNECTION CONFIGURATION

    $host = '127.0.0.1';
    $db_name = 'chat_app';
    $db_user = 'root';
    $db_pass = '';

    $dsn = 'mysql:host='.$host.';dbname='.$db_name;
    $db_options = [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
    ];

    $pdo = new PDO('mysql:host=localhost;dbname=chat_app;', $db_user, $db_pass, $db_options);