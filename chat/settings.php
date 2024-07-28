<?php
    session_start(); 
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
    }

    $pass = $npass = $cpass = "";
    $error = [
        'pass' => '',
        'npass' => '',
        'cpass' => ''
    ];

    if(isset($_POST['submit'])) {
        $pass = $_POST['password'];
        $npass = $_POST['npassword'];
        $cpass = $_POST['cpassword'];

        if(empty($pass)) {
            $error['pass'] = "Current password is required";
        } else {
            try {
                require('../config/pdo.php');
                
                $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
                $stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();

                $row = $stmt->fetch();
                if($row['password'] !== sha1($pass)) {
                    $error['pass'] = 'Current password is incorrect';
                }

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }

        if(empty($npass)) {
            $error['npass'] = "New password is required";
        } else {
            if($npass !== $cpass) {
                $error['npass'] = "Passwords do not match";
            }
        }

        if(empty($pass)) {
            $error['cpass'] = "Confirmation password is required";
        }

        if(!array_filter($error)) {
            try {
                require('../config/pdo.php');
                
                $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE username = :username');
                $stmt->bindValue(':password', sha1($npass), PDO::PARAM_STR);
                $stmt->bindValue(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();

                $pass = $npass = $cpass = "";

                $msg['success'] = "Password has been updated successfully";

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App | Settings</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="chat-container">
        <header>
            <h2>Chat App</h2>
            <nav>
                <a href="index.php">Home</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        <div class="settings">
            <div class="card">
                <div class="card-header">
                    <p>Settings > Update Password</p>
                </div>
                <div class="set-user">
                    <p><b>Username:</b> <?=$_SESSION['username']?></p>
                    <p><b>Member Since: </b> <?=date('jS F Y - H:i:s', strtotime($_SESSION['date']))?></p>
                </div>
                <form action="" method="post">
                    <div class="input">
                        <label for="password">Current Password</label>
                        <input type="password" name="password" value="<?=$pass?>">
                        <p class="error"><?=$error['pass']?></p>
                    </div>
                    <div class="input">
                        <label for="password">New Password</label>
                        <input type="password" name="npassword"  value="<?=$npass?>">
                        <p class="error"><?=$error['npass']?></p>
                    </div>
                    <div class="input">
                        <label for="password">Confirm New Password</label>
                        <input type="password" name="cpassword"  value="<?=$cpass?>">
                        <p class="error"><?=$error['cpass']?></p>
                    </div>
                    <div class="input">
                        <input type="submit" name="submit" value="Update">
                    </div>
                    <p class="success" style="text-align: center; color: green">
                        <?php if(isset($msg['success'])) {
                            echo $msg['success'];
                        } ?>
                    </p>
                </form>
            </div>
        </div>
    </div>