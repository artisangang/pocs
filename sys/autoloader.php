<?php

/**
 * Class Autoloader
 */
class Autoloader
{

    /**
     * @var array
     * store registered paths
     */
    protected $paths = [];

    /**
     * register auto loader
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * @param $class
     * class auto loader method
     */
    protected function loadClass($class)
    {


        foreach ($this->paths as $prefix => $path) {


            $len = strlen($prefix);

            if (strncmp($prefix, $class, $len) !== 0) {
                // no, move to the next registered autoloader
                return;
            }

            $relative_class = substr($class, $len);


            $file = $path . str_replace('\\', '/', $relative_class) . '.php';

            // if the file exists, require it
            if (is_readable($file)) {
                include $file;
            }


        }

    }

    /**
     * @param $prefix
     * @param $dir
     * register some path for auto loading
     */
    public function add($prefix, $dir)
    {
        $this->paths[$prefix] = $dir;
    }

    /**
     * un-register auto loader
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

}

/**
 * auto loader initiated
 */
return new Autoloader;


