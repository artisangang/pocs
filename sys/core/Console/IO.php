<?php namespace POCS\Core\Console;

class IO {
	
    /**
     * @param $message
     * print info with data and time to console and log if enable by config
     */
	public static function debug($message) {

        $request = Request::instance();
        $log =  date('[Y-m-d H:i:s] ') . "{$message}\n";

        if (config('verbose', $request->flags('v', false)) === true) {
            echo $log;
        }

        if (config('log', true) === true) {
            file_put_contents(BASEDIR . '/tmp/logs/pocs.log', $log, FILE_APPEND);
        }
    }

     /**
      * @param $info
      * print info to console
      */
     public static function info($info) {
        echo "$info\n";
    }

}