<?php namespace POCS\Core\Console;


/**
 * Class Request
 * @package POCS\Core\Console
 *
 * all of the command line arguments will be passed to this class, this will server as request object
 */
class Request
{

    /**
     * @var array
     * store original argument passed in cli
     */
    private $_originalArguments = [];

    // console instance
    private static $_instance;

    /**
     * @var array
     * collection of extracted cli arguments
     */
    private $_request = [
        'options' => [],
        'flags' => [],
        'commands' => [],
    ];

    /**
     * @param array $arguments
     * extract options, flags and commands from cli arguments
     */
    private function __construct(array $arguments)
    {


        $this->_originalArguments = $arguments;

        foreach ($this->_originalArguments as $arg) {


            // Is it a option?
            if (substr($arg, 0, 2) === '--') {

                $com = substr($arg, 2);
                $value = true;

                // does this option has value
                if (strpos($com, '=')) {
                    list($com, $value) = explode("=", $com, 2);
                }


                $this->_request['options'][$com] = $value;
                continue;

            }

            // Is it a flag?
            if (substr($arg, 0, 1) === '-') {
                $this->_request['flags'][substr($arg, 1)] = true;
                continue;
            }

            // finally, it is not option, nor flag, nor argument
            $this->_request['commands'][] = $arg;
            continue;
        }


    }

    /**
     * @param array $arguments
     * @return mixed
     * single instance provider
     */
    public static function instance(array $arguments = [])
    {
        if (null === static::$_instance) {
            static::$_instance = new static($arguments);
        }

        return static::$_instance;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     * get options
     */
    public function options($key = null, $default = null)
    {

        if (is_null($key)) {
            return $this->_request['options'];
        }

        if (isset($this->_request['options'][$key])) {
            $default = $this->_request['options'][$key];
        }

        return $default;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     * get commands
     */
    public function arguments($key = null, $default = null)
    {

        if (is_null($key)) {
            return $this->_request['commands'];
        }

        if (!isset($this->_request['commands'][$key])) {
            $default = $this->_request['commands'][$key];
        }

        return $default;
    }

    /**
     * @param null $key
     * @param null $default
     * @return null
     * get flags
     */
    public function flags($key = null, $default = null)
    {

        if (is_null($key)) {
            return $this->_request['flags'];
        }

        if (isset($this->_request['flags'][$key])) {
            $default = $this->_request['flags'][$key];
        }

        return $default;
    }
  

}