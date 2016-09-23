<?php 

session_start();

error_reporting(E_ALL);


 define('BASEDIR', __DIR__ . '/..');


// get autoloader in
$autoload = require BASEDIR . '/sys/autoloader.php';


// get helpers in
require BASEDIR . '/sys/helpers.php';

$autoload->add('POCS\\Core\\', BASEDIR . '/sys/core/');
$autoload->add('POCS\\Lib\\', BASEDIR . '/lib/');

// register autoloader
$autoload->register();

$service = \POCS\Core\Service::instance([\POCS\Lib\PDO\ServiceProvider::class]);

// create config
$config = \POCS\Core\Config::instance();

if (!empty($_GET['get'])) {
    
    if (!empty($_GET['user']) && !empty($_GET['receiver'])) {



        $messages = \POCS\Core\Service::DB()->query("select chat_history.*, sender.id , sender.username as sender_username, receiver.id , receiver.username as receiver_username from chat_history left JOIN users as sender on chat_history.sender_id = sender.id left JOIN users as receiver on chat_history.receiver_id = receiver.id where (chat_history.receiver_id = ? and chat_history.sender_id = ?) or (chat_history.receiver_id = ? and chat_history.sender_id = ?)  order by chat_history.created_at desc limit 0,10", [$_GET['user'], $_GET['receiver'], $_GET['receiver'], $_GET['user']]);



        header('Content-Type: application/json');
        $messages = $messages->fetchAll();
        if (!empty($messages)) {
            
            $message_final = [];

            for($i=(count($messages)-1); $i>=0; $i--) {
                    array_push($message_final, $messages[$i]);
            }

            $messages = $message_final;

        }
        echo json_encode($messages, true);
        exit;
        
    }

}

if (isset($_GET['logout'])) {
    session_destroy();
      header('location: index.php');
      exit;
}

if ($_POST) {
    
     $user = \POCS\Core\Service::DB()->from('users')->where('username', $_POST['username'])->first();

   
        if (!$user || !password_verify($_POST['password'], $user->password)) {
            unset($_SESSION['user']);
            header('location: index.php');
            exit;
        }

        $_SESSION['user'] = $user->toArray();
       
        header('location: index.php?user='.$user->id);
        echo '<script> window.location = "index.php?user='.$user->id.'"; </script>';
        exit;

}


?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>PHP Open Chat Server</title>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="style.css" type="text/css" rel="stylesheet">

</head>
<body>

<header id="top" class="bs-docs-nav navbar navbar-static-top navbar-inverse">
<div class="container-fluid">
    <div class="navbar-header"> 
        <a class="navbar-brand" href="index.php">POCS v1.0</a>
    </div>
    <?php if (!empty($_SESSION['user'])) : ?>
    <nav id="bs-navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php?logout" class="pull-right">Logout!</a></li> 
        </ul>
    </nav>
    <?php endif; ?>
</div>
</header>

<div class="container-fluid">

<?php if (empty($_SESSION['user'])) : ?>

    <div class="col-sm-12">

        <div class="col-sm-4 col-sm-offset-4">
            <form class="form-horizontal" method="post">

            <h4>Please provide your login credentials</h4>

                <input type="text" name="username" placeholder="Your username" class="form-control"><br>
                <input type="password" name="password" class="form-control" placeholder="Your password"><br>
                <button type="submit" class="btn  btn-primary">Login</button>
            </form>
        </div>

    </div>
<?php else: ?>    

  <?php


        $user = \POCS\Core\Service::DB()->from('users')->where('id', $_SESSION['user']['id'])->first();
        $uid = $user->id;
        if (!$user) {
            header('location: index.php?err=u404');
            exit;
        }

        $users = \POCS\Core\Service::DB()->from('users')->where('id', '!=', $user->id)->all();

    ?>

<div class="col-sm-9 col-xs-8">

        

        <div id="log"></div>

        <div>
        <div class="clearfix">
        <br>
        </div>
            <div id="statusTxt" class="alert hidden"></div>
            <div class="input-group">
                <input id="msg" type="textbox" onkeypress="onkey(event)" placeholder="Type your message here..."  class="form-control">
                <span class="input-group-btn">
                    <button onclick="send()" class="btn btn-primary">send</button>
                </span>
            </div>
        </div>
   
</div>

<div class="col-sm-3 col-xs-4">
    
        <ul id="clients">

        <?php if (!empty($users)) : ?>
            
            <?php  foreach ($users as $user): ?>
                <li data-id="<?= $user->id ?>" id="user-<?= $user->id  ?>" class="connect-user"><?= $user->username ?></li>
            <?php endforeach; ?>

        <?php endif; ?>

        </ul>

</div>


<script src="jquery-1.12.4.min.js"></script>
<script type="text/javascript">

    // uid  is sender and  cid is receiver
   var uid = <?= $uid ?>, host = '<?= config('ip') ?>', port = <?= config('port') ?>;
   

</script>
<script src="websocket.js"></script>

<?php endif; ?>
</div>
</body>
</html>