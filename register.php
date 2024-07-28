<?php
    session_start();

    if(isset($_SESSION['user_id'])) {
        header('location: chat/index.php');
    }

    $error = [
        'username' => '',
        'password' => '',
        'cpassword' => ''
    ];

    $username = $password = $cpassword = '';

    if(isset($_POST['submit'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        if(empty($username)) {
            $error['username'] = 'Username is required';
        } else {
            try {
                require('config/pdo.php');
                
                $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
                $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                $stmt->execute();

                if($stmt->rowCount() > 0) {
                    $error['username'] = "Username already taken";
                }

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }

        if(empty($password)) {
            $error['password'] = 'Password is required';
        }

        if(empty($cpassword)) {
            $error['cpassword'] = 'Confirmation Password is required';
        } else {
            if($cpassword != $password) {
                $error['password'] = 'Passwords do not match';
            }
        }

        if(!array_filter($error)) {
            # INSERTING TO DATABASE #
            try {
                $stmt = $pdo->prepare('INSERT INTO users(username, password) VALUE (:username, :password)');
                $stmt->execute(['username'=>$username, 'password'=>sha1($password)]);
                $stmt->rowCount() > 0 ? $msg['success'] = "User created successfully" : $error['error'] = "There was an error while creating a user";
                $username = $password = $cpassword = '';
            } catch(Exception $e) {
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
    <title>Chat App | Registration</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="register">
            <h2>Chat App Register</h3>
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
                <div class="input">
                    <label for="cpassword">Confirm Password</label>
                    <input type="password" name="cpassword" value="<?=$cpassword?>">
                    <p class="error"><?=$error['cpassword']?></p>
                </div>
                <input type="submit" name="submit" value="Register">
            </form>
                <?php if(isset($msg['success'])) :?>
                    <p class="success">
                        <?=$msg['success'];?>
                    </p>
                <?php elseif(isset($error['error'])): ?>
                    <span class="error">
                        <?=$error['error'];?>
                    </span>
                <?php endif ?>
            <p>Already registerd? Login <a href="login.php">here</a></p>
        </div>
    </div>
</body>
</html>