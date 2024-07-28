<?php
    session_start();

    if(isset($_SESSION['user_id'])) {
        header('location: chat/index.php');
    }

    # DECLARING ERROR ARRAY TO STORE ERRORS #

    $error = [
        'username' => '',
        'password' => '',
        'cpassword' => ''
    ];

    $username = $password = $cpassword = '';

    if(isset($_POST['submit'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if(empty($username)) {
            $error['username'] = 'Username is required';
        } else {
            try {
                require('config/pdo.php');
                
                $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
                $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                $stmt->execute();

                if($stmt->rowCount() == 0) {
                    $error['username'] = "Username doesn't exist";
                } else {
                    global $row;
                    $row = $stmt->fetch();
                    if($row['password'] !== sha1($password)) {
                        $error['password'] = 'Password is incorrect';
                    }
                }

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }

        if(empty($password)) {
            $error['password'] = 'Password is required';
        }

        if(!array_filter($error)) {
            # ASSIGNING SESSION VARIABLES TO USER #
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['date'] = $row['created_at'];

            # REDIRECTING USER TO THE CHATS #
            header('location: chat/index.php');
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App | Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="login">
            <h2>Login to Chat App</h3>
            <form action="" method="post">
                <div class="input">
                    <label for="Username">Username</label>
                    <input type="text" name="username" value="<?=$username?>">
                    <p class="error"><?=$error['username']?></p>
                </div>
                <div class="input">
                    <label for="password">Password</label>
                    <input type="password" name="password" value="<?=$password?>">
                    <p class="error"><?=$error['password']?></p>
                </div>
                <input type="submit" name="submit" value="Login">
                <p>Don't have an account? Register <a href="register.php">here</a></p>
            </form>
        </div>
    </div>
</body>
</html>