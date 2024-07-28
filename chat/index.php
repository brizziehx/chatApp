<?php
    session_start(); 
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
    }

    if(isset($_POST['submit'])) { 
        $outgoing_id = $_POST['outgoing_id'];
        $incoming_id = $_POST['incoming_id'];
        $message = trim($_POST['message']);

        if(!empty($message) && strlen($message) <= 255) {
            try {
                require('../config/pdo.php');

                $stmt = $pdo->prepare("INSERT INTO messages(outgoing_msg_id, incoming_msg_id, message) VALUE (:OMI, :IMI, :msg)");
                $stmt->bindValue(':OMI', $outgoing_id, PDO::PARAM_INT);
                $stmt->bindValue(':IMI', $incoming_id, PDO::PARAM_INT);
                $stmt->bindValue(':msg', $message, PDO::PARAM_STR);
                $stmt->execute();

                $message = '';
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
    <title>Chat App | Home</title>
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
        <aside>
            <!-- USERS LISTS  -->
            <div class="users-list">
            <?php
                try {
                    require('../config/pdo.php');
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id <> :user_id');
                    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->execute();

                    if($stmt->rowCount() > 0):
                        $rows = $stmt->fetchAll();
                        foreach($rows as $row): ?>
                            <a href="index.php?user_id=<?=$row['user_id']?>">
                                <?=$row['username']?> 
                            </a>
                <?php  endforeach;
                    else:
                        echo "<a style='display: block; margin-top:20px'>No users to chat with...</a>";
                    endif;
                } catch(PDOException $e) {
                    echo $e->getMessage();
                } ?>
      
            </div>
        </aside>
        <main>
            <div class="chat-content">
                <?php
                    $user_id = $_GET['user_id'] ?? '';
                    try {
                        require('../config/pdo.php');
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
                        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt->execute();

                        $row = $stmt->fetch(); ?>

                        <div class="header">
                            <a href="#"><?=$row['username'] ?? 'No user selected'?></a>
                        </div>

                <?php    } catch(PDOException $e) {
                        echo $e->getMessage();
                    }
                ?>
                <div class="messages">
                    <?php
                        try {
                            require('../config/pdo.php');
                            if(!empty($user_id)):
                                $outgoing_id = $_SESSION['user_id'];
                                $incoming_id = $user_id;

                                $stmt = $pdo->prepare("SELECT * FROM messages LEFT JOIN users ON users.user_id = messages.outgoing_msg_id WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id}) OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) ORDER BY msg_id");
                                $stmt->execute();
                                $output = '';

                                if($stmt->rowCount() > 0) {
                                    $rows = $stmt->fetchAll();
                                    foreach($rows as $row) {
                                        if($row['outgoing_msg_id'] === $outgoing_id) {
                                            $output .= "
                                                <div class='sent' style='margin-top: 10px'>
                                                    <div class='user'>
                                                        ".$row['username']."
                                                        <a href=delete_message.php?msg_id=".$row['msg_id']."&user_id=".$incoming_id.">
                                                            <svg style='margin-left: 5px; cursor: pointer;' xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24'><path d='M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z'/><path d='M9 10h2v8H9zm4 0h2v8h-2z'/></svg>
                                                        </a>
                                                    </div>
                                                    <div class='msg-content'>".$row['message']."</div>
                                                    <span style='display: flex; align-items: center;'>".date('jS F Y H:i', strtotime($row['date']))."</span>
                                                </div>
                                            ";
                                        } else {
                                            $output .= "
                                                <div class='incoming'>
                                                    <div class='user'>
                                                        ".$row['username']."
                                                    </div>
                                                    <div class='msg-content'>".$row['message']."</div>
                                                    <span>".date('jS F Y H:i', strtotime($row['date']))."</span>
                                                </div>
                                            ";
                                        }
                                    }
                                } else {
                                    $output .= "<p style='display: flex; min-height: 65vh; justify-content: center; align-items: center'> No messages available</p>";
                                }
                                echo $output;
                            else: ?>
                                <p style="display: flex; flex-direction: column; min-height: 50vh; justify-content: center;align-items: center;">
                                    Welcome to Chat App..

                                    <span>
                                        Select user to initiate a chat
                                    </span>
                                </p>
                            <?php endif;
                            } catch (PDOException $e) {
                                echo $e->getMessage();
                            }
                    ?>
                </div>
                <div class="input">
                    <?php if(!empty($user_id)) : ?>
                        <form action="" method="post">
                            <input type="text" name="outgoing_id" value="<?php echo $_SESSION['user_id']; ?>" hidden>
                            <input type="text" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
                            <textarea type="text" id="message" name="message" placeholder="Type a message"><?=$message ?? ''?></textarea>
                            <input type="submit" name="submit" value="Send">
                        </form>
                    <?php endif ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>