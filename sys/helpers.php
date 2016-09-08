<?php

/**
 * @param $array
 * @param $key
 * @param null $default
 * @return mixed
 * helper to get array value, dot(.) notation supported
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }
    if (isset($array[$key])) {
        return $array[$key];
    }
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return value($default);
        }
        $array = $array[$segment];
    }
    return $array;
}

/**
 * @param $key
 * @param null $default
 * @return mixed
 * config helper
 */
function config($key, $default = null)
{
    if (!class_exists('POCS\Core\Config')) {
        throw new RuntimeException("Config is not initiated.");
    }

    return \POCS\Core\Config::instance()->get($key, $default);
}
