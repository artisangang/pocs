var receiver_id, socket;

jQuery(function ($) {

	 $(".connect-user").click(function () {
        receiver_id = $(this).data('id');
        $.ajax({
            url:'index.php',
            type:'get',
            data: {get:'chat', user: uid, 'receiver': receiver_id},
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

	 try {

	 		socket = new WebSocket('ws://' + host + ':' + port);

	 		socket.onopen = function (e) {

	 			status( "<strong>Connected: </strong> You are now connected to server.", 'alert-success');

                var data = {intent:'authenticate', type: 'list', 'uid':uid };
                socket.send(JSON.stringify(data));

            };

            socket.onmessage = function (e) {

            	var payloads = JSON.parse(e.data);


                if (typeof payloads.alert != 'undefined') {
                    status( "<strong>Notification: </strong> " + payloads.alert, 'alert-info');
                }

                 if (typeof payloads.from != 'undefined' && typeof payloads.text != 'undefined' && typeof payloads.sender_id != 'undefined') {

                    if (payloads.sender_id != receiver_id) {
                        status('<strong>'+payloads.from+' says: </strong>' + payloads.text, 'alert-info');
                    } else {
                        log( payloads.from + ': ' + payloads.text);
                    }
                }



            };

             socket.onclose = function (e) {
             	status( "<strong>Disconnected: </strong> You are connection closed.", 'alert-warning');
             };

        } catch (ex) {
            console.log(ex);
        }

});

function closed(e) {

	var reason;

    // See http://tools.ietf.org/html/rfc6455#section-7.4.1
    if (e.code == 1000)
        reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
    else if (e.code == 1001)
        reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
    else if (e.code == 1002)
        reason = "An endpoint is terminating the connection due to a protocol error";
    else if (e.code == 1003)
        reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
    else if (e.code == 1004)
        reason = "Reserved. The specific meaning might be defined in the future.";
    else if (e.code == 1005)
        reason = "No status code was actually present.";
    else if (e.code == 1006)
        reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
    else if (e.code == 1007)
        reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
    else if (e.code == 1008)
        reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
    else if (e.code == 1009)
        reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
    else if (e.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
        reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + e.reason;
    else if (e.code == 1011)
        reason = "A server is terminating the connection because it encountered an unexpected condition that preed it from fulfilling the request.";
    else if (e.code == 1015)
        reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
    else
        reason = "Unknown reason";

   status( "<strong>Error: [" + e.code + "] </strong>" + reason , 'alert-danger');

}

function status(msg, cls) {

    var cls = cls || "alert-info";

    $("#statusTxt").removeClass('hidden').addClass(cls).html(msg);
}

function send() {

  if (!receiver_id) {
    alert('Select user to send message.');
    return;
  } 
  
  var txt = $("#msg").val();

  if (txt.trim() == '') {
    alert("Message can't be left blank.");
    return;
  }    


  var payload =  {intent:'communicate',  text: txt, receiver_id: receiver_id};

  socket.send(JSON.stringify(payload));

  $("#msg").val('');

  log("You: " + txt);

}

function onkey() {

}

function log(msg) {
    $("#log").append('<p class="msg-line">' + msg + '</p>');
}

