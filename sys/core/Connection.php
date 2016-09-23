<?php namespace  POCS\Core;

use \POCS\Core\Console\IO as Log;


class Connection {

	protected $bufferSize = 4096;

	protected $socket;

    protected $handshake = false;

    protected $headers = [];

    protected $resourceId;

    protected $payloads;

    protected $vars;

    private $_master;

    /**
     * @param $socket
     * @param \POCS\Core\Socket $master
     */
	public function __construct($socket, Socket $master) {
		$this->socket = $socket;
        $this->resourceId = uniqid();
        $this->_master = $master;
	}

    /**
     * @return mixed
     * connection unique id
     */
    public function resourceId() {
        return $this->resourceId;
    }
    
    /**
     * @return mixed
     * connection socket
     */
    public function socket() {
        return $socket;
    }

    /**
     * @return array $this->payloads
     */
    public function payloads() {
        return $this->payloads;
    }

    /**
     * @param $key
     * @param $data
     * save data in connection instance
     */
    public function assign($key, $data) {
        $this->vars[$key] = $data;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     * provide saved data
     */
    public function get($key, $default = null) {
        return isset($this->vars[$key]) ? $this->vars[$key] : $default;
    }

    /**
     * do handshake with client
     */
    public function doHandshake() {

        if (true === $this->handshake) {
            return false;
        }

         $buffer = stream_socket_recvfrom($this->socket, 1024, 0);

          if ('' !== $buffer && false !== $buffer) {
            
           

            $socket  = $this->socket;

            //$buffer = stream_socket_recvfrom($socket, $this->bufferSize, 0);

             $guid = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

             $lines = explode("\n",$buffer);


            foreach ($lines as $line) {
                if (strpos($line,":") !== false) {
                    $header = explode(":",$line,2);
                    $this->headers[strtolower(trim($header[0]))] = trim($header[1]);
                }
                elseif (stripos($line,"get ") !== false) {
                    preg_match("/GET (.*) HTTP/i", $buffer, $reqResource);
                    $this->headers['get'] = trim($reqResource[1]);
                }
            }


            if (!isset($this->headers['get'])) {
                // todo: fail the connection
                $handshakeResponse = "HTTP/1.1 405 Method Not Allowed\r\n\r\n";
            }
            if (!isset($this->headers['host']) || !in_array($this->headers['host'], config('allow', [$this->headers['host']]) ) ) {
                $handshakeResponse = "HTTP/1.1 400 Bad Request";
            }
            if (!isset($this->headers['upgrade']) || strtolower($this->headers['upgrade']) != 'websocket') {
                $handshakeResponse = "HTTP/1.1 400 Bad Request";
            }
            if (!isset($this->headers['connection']) || strpos(strtolower($this->headers['connection']), 'upgrade') === FALSE) {
                $handshakeResponse = "HTTP/1.1 400 Bad Request";
            }
            if (!isset($this->headers['sec-websocket-key'])) {
                $handshakeResponse = "HTTP/1.1 400 Bad Request";
            }
            if (!isset($this->headers['sec-websocket-version']) || $this->headers['sec-websocket-version'] != 13) {
                $handshakeResponse = "HTTP/1.1 426 Upgrade Required\r\nSec-WebSocketVersion: 13";
            }

            if (isset($handshakeResponse)) {
                Log::info($handshakeResponse);
                fwrite($this->socket, $handshakeResponse);
                fclose($this->socket);
                stream_socket_shutdown($this->socket,  STREAM_SHUT_RDWR);
                return;
            }

            $webSocketKeyHash = sha1($this->headers['sec-websocket-key'] . $guid);
            $rawToken = "";
            for ($i = 0; $i < 20; $i++) {
                $rawToken .= chr(hexdec(substr($webSocketKeyHash,$i*2, 2)));
            }
            $handshakeToken = base64_encode(pack('H*', $webSocketKeyHash)) . "\r\n";


            $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: {$handshakeToken}\r\n";

            $this->handshake = true;

            

            fwrite($this->socket, $handshakeResponse);
        }

    }

    /**
     * write data to client socket
     */
    public function write(array $payloads) {
        fwrite($this->socket, $this->encode(json_encode($payloads)));
    }


    /**
     * @return mixed
     * read socket
     */
    public function read() {

        if ($data = stream_socket_recvfrom($this->socket, $this->bufferSize, 0)) {
            
            $this->payloads = json_decode($this->decode($data));
            return $this->payloads;
        } 

        return '';

    }

    /**
     * close client connection
     */
    public function close() {
         if (is_resource($this->socket)) {
            stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
            fclose($this->socket);
            $this->_master->purge($this->resourceId);
            $this->app->controller()->disconnected($this);
        }
    }


    /**
     * @param $message
     * @param mixed $messageType
     * @return mixed
     * encode data for websocket
     */
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

     /**
     * @param $payload
     * @return mixed
     * decode data received from websocket
     */
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


     /**
     * @param $resource
     * @param array $payloads
     * forward stream to another client
     */
    public function forward($resource, array $payloads) {
        if ($c = $this->_master->connection($resource)) {
            $c->write($payloads);
        } else {
            // notify user that resource is offline or invalid
        }
    }

}