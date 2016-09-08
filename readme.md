# PHP Open Chat Server (POCS)

POCS is a php framework to create chatting/instant messaging application.

## Requirements

- PHP >= 5.6.4
- WebSocket Supported Browser

**Note: make sure php_sockets extension is enabled.**

## Usage

Copy And Extract the cloned/downloaded archive in pocs under xampp/wampp (Any preffered server) server root (htdocs in  case of xampp) directory. Open your terminal, run command: php pocs (make sure php is in global path). Now open you browser, navigate to public directory in application path (In case you have extracted files in pocs, http://localhost/pocs/public). You can communicate with other user using client id. When user connected to server, unique client id will be assigned.

## Example Communication 

Browse http://localhost/pocs/public in mozilla/chrome. You will see below message:

Connecting to server please wait...
Handshake successfully done...
Server: Your are connected to server with #[u57d1850c54390]... 


Now u57d1850c54390 is you client id.

Open new browser (if first one is mozilla then browse same link in chrome), Browse http://localhost/pocs/public. You will See below message:


Connecting to server please wait...
Handshake successfully done...
Server: Your are connected to server with #[u57d1852519be7]...


Now new client is connected to server with id u57d1852519be7. You can comminicate from one browser to another vice-versa using client id (Mozilla to Chrome - Chrome to Mozilla).

For comminication copy paste client id (Id of client to whom you wants to communicate) in clientID input and type you message in message input, then click send.
