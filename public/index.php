<?php error_reporting(E_ALL);


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
        echo json_encode($messages->fetchAll());
        exit;
        
    }

}

if ($_POST) {
    
     $user = \POCS\Core\Service::DB()->from('users')->where('username', $_POST['username'])->first();

   
        if (!$user || !password_verify($_POST['password'], $user->password)) {
            unset($_SESSION['user']);
            header('location: index.php');
            echo '<script> window.location = "index.php"; </script>';
            exit;
        }
       
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
    <style type="text/css">
        html, body {
            font: normal 13px arial, helvetica;
            margin: 0;
            padding: 0;
        }

        h3 {
            float: left;
            width: 100%;
            display: block;
            padding: 10px;
        }

        #col-left {
            float: left;
            width: 70%;
            height: 100%;

        }

        #col-right {
            float: left;
            width: 30%;
            height: 100%;

        }

        #log {
            float: left;
            width: 100%;
            height: 300px;
            border: 1px solid #7F9DB9;
            overflow-x: scroll;
        }

        #msg {
            width: 99%;
            float: left;
        }



        .pad-20 {
            padding: 20px;
        }

        #clients {
            border: 1px solid #7F9DB9;
            height: 300px;
            margin: 0;
            list-style: none;
            padding-left: 0;
        }

        #clients li {
            padding: 10px;
            color: #ccc;
            cursor: pointer;
        }

        #clients li.selected {
            color: #222;
            background-color: #e7e7e7;
        }

        .msg-line {
            background: #f7f7f7 none repeat scroll 0 0;
            display: inline-block;
            float: left;
            line-height: 15px;
            margin: 0;
            padding: 0.50%;
            width: 99%;
        }
    </style>


</head>
<body onload="init()">

<h3>POCS v1.0</h3>

<?php if (empty($_GET['user'])) : ?>

    <form style="padding:20px" method="post">
        <input type="text" name="username" placeholder="Your username"><br>
        <input type="password" name="password"><br>
        <button type="submit">Login</button>
    </form>


<?php else: ?>    

  <?php

        $user = \POCS\Core\Service::DB()->from('users')->where('id', $_GET['user'])->first();

        $uid = $user->id;

        if (!$user) {
            header('location: index.php');
            echo '<script> window.location = "index.php"; </script>';
            exit;
        }

        $users = \POCS\Core\Service::DB()->from('users')->where('id', '!=', $user->id)->all();

    ?>

<div id="col-left">

    <div class="pad-20">
        <div id="log"></div>

        <div>
            <input id="msg" type="textbox" onkeypress="onkey(event)" placeholder="Type your message here...">
        </div>
        <br><br>
        <button onclick="send()">send</button>
        <button onclick="quit()">Quit</button>
    </div>
</div>

<div id="col-right">
    <div class="pad-20">
        <ul id="clients">

        <?php if (!empty($users)) : ?>

            <?php  foreach ($users  as $user): ?>
                <li data-user="<?= $user->id ?>" id="user-<?= $user->id  ?>" class="connect-user"><?= $user->username ?></li>
            <?php endforeach; ?>

        <?php endif; ?>

        </ul>
    </div>
</div>

<script src="jquery-1.12.4.min.js"></script>

<script type="text/javascript">

    // uid  is sender and  cid is receiver
    var socket, uid, receiver, sender = <?= $uid ?>;

    $(".connect-user").click(function () {
        receiver = $(this).data('user');
        $.ajax({
            url:'index.php',
            type:'get',
            data: {get:'chat', user: <?= $uid ?>, 'receiver': receiver},
            success: function (o) {
                $('#log').html('');
                if (o.length) {
                    for (var i in o) {
                       
                        log(o[i].sender_username + ': ' + o[i].message);
                    }
                }

               

            }

        });
    });

  
 
    function init() {
        var host = "ws://127.0.0.1:9000/user/<?= $uid; ?>";
        try {
            socket = new WebSocket(host);
            log('Connecting to server please wait...');

            socket.onopen = function () {
                log('Handshake successfully done...');
            };


            socket.onmessage = function (e) {

                var payloads = JSON.parse(e.data);

                if (typeof uid == 'undefined') {
                    uid = payloads.uid;
                }

                var from = (typeof payloads.from != 'undefined') ? payloads.from : payloads.cid;

                if (typeof from == 'undefined') {
                    from = 'Server';
                }

                var message = from +': ' + payloads.text;
                log(message);

                $("#log").scrollTop($("#log")[0].scrollHeight);

            };


            socket.onclose = function (event) {


                var reason;

                // See http://tools.ietf.org/html/rfc6455#section-7.4.1
                if (event.code == 1000)
                    reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
                else if (event.code == 1001)
                    reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
                else if (event.code == 1002)
                    reason = "An endpoint is terminating the connection due to a protocol error";
                else if (event.code == 1003)
                    reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
                else if (event.code == 1004)
                    reason = "Reserved. The specific meaning might be defined in the future.";
                else if (event.code == 1005)
                    reason = "No status code was actually present.";
                else if (event.code == 1006)
                    reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
                else if (event.code == 1007)
                    reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
                else if (event.code == 1008)
                    reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
                else if (event.code == 1009)
                    reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
                else if (event.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
                    reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
                else if (event.code == 1011)
                    reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
                else if (event.code == 1015)
                    reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
                else
                    reason = "Unknown reason";

                log("Disconnected: [" + event.code + "] " + reason);

            };

        }
        catch (ex) {
            log(ex);
        }


    }

    function send(message) {

        if (typeof message == 'undefined') {
            var txt, msg;
            txt = $("#msg");
            msg = txt.val();
            
            if (!receiver) {
                alert("Please select user first");
                return;
            }

            if (!msg) {
                alert("Message can not be empty");
                return;
            }


            txt.value = "";
            txt.focus();
        } else {
            msg = message;
        }

        try {
            var payload = {
                'receiver': receiver, // receiver
                'uid': uid, // sender
                'sender': sender,
                text: msg
            };

            socket.send(JSON.stringify(payload));

            log('you: ' + msg);
            $("#log").scrollTop($("#log")[0].scrollHeight);

        } catch (ex) {
            log(ex);
        }
    }



    function quit() {
        if (socket != null) {
            log("Goodbye!");
            socket.close();
            socket = null;
        }
    }


    // Utilities

    function log(msg) {
        $("#log").append('<p class="msg-line">' + msg + '</p>');
    }
    function onkey(event) {
        if (event.keyCode == 13) {
            send();
        }
    }
</script>

<?php endif; ?>

</body>
</html>