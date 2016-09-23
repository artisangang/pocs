# PHP Open Chat Server (POCS)

POCS is a php framework to create chatting/instant messaging application.

## Requirements

- PHP >= 5.6.4
- WebSocket Supported Browser


## Options

**flags**

- -d: for debugging
- -v: to output logs in console

**options**

- --env: to set environment (for example: --env=test)
- --ip: to set ip address
- --port: to set port

## Usage

Copy And Extract the cloned/downloaded archive in pocs under xampp/wampp (Any preferred server) server root (htdocs in  case of xampp) directory. Open your terminal, run command: php pocs (make sure php is in global path). Now open you browser, navigate to public directory in application path (In case you have extracted files in pocs, http://localhost/pocs/public). You can communicate with other user by click them (List will be displayed in right side). When selected user is not connected to server, chat will be saved to databsde.

## Example Communication 

Browse http://localhost/pocs/public in mozilla/chrome.

**Use credentials: username: harry and password: 123456**


Open new browser (if first one is mozilla then browse same link in chrome), Browse http://localhost/pocs/public. 

**Use credentials: username: nitin and password: 123456**

You will See below message:


You can communicate from one browser to another vice-versa by selecting user from (Users list will be displayed in right side of communication panel) right side. (Mozilla to Chrome - Chrome to Mozilla).

For communication just click to select user from users list and type you message in message input, then click send or press enter.
