<?php namespace POCS\Core;

class Socket {


    protected $server;

    protected $options = [
        ['level' => SOL_SOCKET, 'option' => SO_REUSEADDR, 'value' => 1]
    ];


    protected $maxBufferSize = 2048;

    protected $conn;

    protected $socketConnections = [];


    function __construct($server) {

        $this->server = $server;
    }

    public function conn() {
        return $this->conn;
    }

    public function create() {
        $this->conn = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->conn) {
            $err_code = socket_last_error();
            $err_msg = socket_strerror($err_code);
            throw new \RuntimeException("Unable to create socket: [$err_code] $err_msg.");
        }
        foreach ($this->options as $option) {
            socket_set_option($this->conn, $option['level'], $option['option'], $option['value']);
        }
        socket_bind($this->conn, $this->server->getIp(), $this->server->getPort());
        socket_listen($this->conn,20);

    }


    protected function connect($socket) {



        $client = new Client(uniqid('C'), $socket);
        $ip = $this->server->getIp();
        $port = $this->server->getPort();
        //display information about the client who is connected
        if(socket_getpeername($socket , $ip, $port))
        {

        }

        $this->server->setClient($client);
        $this->socketConnections[$client->getId()] = $socket;
        


        $this->server->getClientController()->connecting($client->getId());


    }

    protected function disconnect(Client $client) {



        $client->setIsConnected(false);

        if($client->getSocket()) {

            socket_shutdown($client->getSocket(), 2);
            socket_close($client->getSocket());

        }

        $this->server->removeClient($client);

        $this->server->getClientController()->disconnected($client->getId(), $client->requestedResource);

       


    }

    public function handshake($client, $buffer) {

        $guid = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

        $headers = array();

        $lines = explode("\n",$buffer);

        foreach ($lines as $line) {

            if (strpos($line,":") !== false) {
                $header = explode(":",$line,2);
                $headers[strtolower(trim($header[0]))] = trim($header[1]);
            }
            elseif (stripos($line,"get ") !== false) {
                preg_match("/GET (.*) HTTP/i", $buffer, $reqResource);
                $headers['get'] = trim($reqResource[1]);
            }
        }

        if (isset($headers['get'])) {
            $client->requestedResource = $headers['get'];
        }
        else {
            // todo: fail the connection
            $handshakeResponse = "HTTP/1.1 405 Method Not Allowed\r\n\r\n";
        }

        if (!isset($headers['host']) || !in_array($headers['host'], config('allow', [$headers['host']]) ) ) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['upgrade']) || strtolower($headers['upgrade']) != 'websocket') {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['connection']) || strpos(strtolower($headers['connection']), 'upgrade') === FALSE) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }
        if (!isset($headers['sec-websocket-key'])) {
            $handshakeResponse = "HTTP/1.1 400 Bad Request";
        }

        if (!isset($headers['sec-websocket-version']) || strtolower($headers['sec-websocket-version']) != 13) {
            $handshakeResponse = "HTTP/1.1 426 Upgrade Required\r\nSec-WebSocketVersion: 13";
        }


        if (isset($handshakeResponse)) {
            Console::log($handshakeResponse);
            socket_write($client->getSocket(),$handshakeResponse,strlen($handshakeResponse));
            $this->disconnect($client);
            return;
        }

        $webSocketKeyHash = sha1($headers['sec-websocket-key'] . $guid);

        $rawToken = "";
        for ($i = 0; $i < 20; $i++) {
            $rawToken .= chr(hexdec(substr($webSocketKeyHash,$i*2, 2)));
        }
        $handshakeToken = base64_encode($rawToken) . "\r\n";

        $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: {$handshakeToken}\r\n";

        socket_write($client->getSocket(),$handshakeResponse,strlen($handshakeResponse));

        $client->setHandshake(true);
       
        $response = $this->server->getClientController()->connected($client->getId(), $client->requestedResource);

        if ($response === Response::DISCONNECT) {
            $this->disconnect($client);
        } elseif ($response) {
            $this->send($client, $response);
        }
        
    }


    public function select() {

        if (empty($this->socketConnections)) {
            $this->socketConnections['m'] = $this->conn;
        }

        $read = $this->socketConnections;

        $write = $except = null;
        @socket_select($read,$write,$except,1);

        foreach ($read as $socket) {


            // for master connection
            if ($socket == $this->conn) {

                Console::log('Processing master socket');

                //Accept incoming connection
                $client = socket_accept($socket);
                if ($client < 0) {
                    Console::log('Failed to accept socket.');
                    continue;
                } else {
                    // create new socket connection client
                    $this->connect($client);
                }

            } else {

                Console::log('Finding the associated  client with current socket...');
                $client = $this->server->getClientBySocket($socket);
                if($client) {
                    Console::log('Receiving data...');
                }



                $bytes = @socket_recv($socket, $buffer, 2048, 0);

                if(!$client->getHandshake()) {

                    $tmp = str_replace("\r", '', $buffer);
                    if (strpos($tmp, "\n\n") === false ) {
                        // If the client has not finished sending the header, then wait before sending our upgrade response.
                        continue;
                    }

                    Console::log("Doing handshake with #{$client->getId()}");
                    $this->handshake($client, $buffer);

                } elseif ($bytes === 0) {
                    Console::log("No byte received.");
                    $this->disconnect($client);
                } else {

                    Console::log('Data received...');

                    // When received data from client
                    $text = $this->decode($buffer);

                    $payloads = json_decode($text, true);

                    $response = $this->server->getClientController()->received($client->getId(), $payloads);
                       if ($response === Response::DISCONNECT) {
                            $this->disconnect($client);
                        } elseif (is_array($response)) {
                          if ($client = $this->server->getClientById($response['cid'])) {
                                Console::log('Receiver found...');
                                $this->send($client, $response);
                            }
                      }  

                }
            }
        }
    }

    public function process() {
        foreach ($this->pendingMessages as $id => $messages) {

            if (empty($messages)) {
                unset($this->pendingMessages[$id]);
                continue;
            }

            if (isset($this->socketConnections[$id])) {
                $client = $this->socketConnections[$id];
                if ($client->getHandshake()) {
                    foreach ($messages as $message) {
                        $this->send($client, $message);
                    }
                }
            }

            unset($this->pendingMessages[$id]);

        }
    }

    public function send($client, $message) {
        if ($client->getHandshake()) {
            Console::log('Sending response...');

            if (is_array($message)) {
                // uid is sender and cid is receiver here
                $payloads = $message;
            } else {
                $payloads = ['uid' => $client->getId(), 'text' => $message];
            }
            $this->server->getClientController()->sending($client->getId(),  $payloads);
            $message = $this->encode(json_encode($payloads));
            @socket_write($client->getSocket(), $message, strlen($message));
            $this->server->getClientController()->sent($client->getId(), $payloads);
        } else {
            // User has not yet performed their handshake.  Store for sending later.
            $this->pendingMessages[$client->getId()][] = $message;
        }
    }

    private function encode($message, $messageType='text') {

        switch ($messageType) {
            case 'continuous':
                $b1 = 0;
                break;
            case 'text':
                $b1 = 1;
                break;
            case 'binary':
                $b1 = 2;
                break;
            case 'close':
                $b1 = 8;
                break;
            case 'ping':
                $b1 = 9;
                break;
            case 'pong':
                $b1 = 10;
                break;
        }

        $b1 += 128;


        $length = strlen($message);
        $lengthField = "";

        if($length < 126) {
            $b2 = $length;
        } elseif($length <= 65536) {
            $b2 = 126;
            $hexLength = dechex($length);
            //$this->stdout("Hex Length: $hexLength");
            if(strlen($hexLength)%2 == 1) {
                $hexLength = '0' . $hexLength;
            }

            $n = strlen($hexLength) - 2;

            for($i = $n; $i >= 0; $i=$i-2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }

            while(strlen($lengthField) < 2) {
                $lengthField = chr(0) . $lengthField;
            }

        } else {

            $b2 = 127;
            $hexLength = dechex($length);

            if(strlen($hexLength) % 2 == 1) {
                $hexLength = '0' . $hexLength;
            }

            $n = strlen($hexLength) - 2;

            for($i = $n; $i >= 0; $i = $i - 2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }

            while(strlen($lengthField) < 8) {
                $lengthField = chr(0) . $lengthField;
            }
        }

        return chr($b1) . chr($b2) . $lengthField . $message;
    }

    private function decode($payload) {
        $length = ord($payload[1]) & 127;

        if($length == 126) {
            $masks = substr($payload, 4, 4);
            $data = substr($payload, 8);
        }
        elseif($length == 127) {
            $masks = substr($payload, 10, 4);
            $data = substr($payload, 14);
        }
        else {
            $masks = substr($payload, 2, 4);
            $data = substr($payload, 6);
        }

        $text = '';
        for($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }
        return $text;
    }


}