<?php
    session_start(); 
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
    }

    try {
        require('../config/pdo.php');

        $stmt = $pdo->prepare("DELETE FROM messages WHERE msg_id = :msg_id");
        $stmt->bindValue(':msg_id', $_GET['msg_id'], PDO::PARAM_INT);
        $stmt->execute();

        header('location: index.php?user_id='.$_GET['user_id']);

    } catch(PDOException $e) {
        echo $e->getMessage();
    }