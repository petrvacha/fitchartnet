<?php

namespace Pushupers\Application;

use Nette\Utils\Strings;

/**
 * Helpful utilities
 */
class Utilities extends \Nette\Object
{
    /**
     * Tring to find PHP_URL_PATH in string
     *
     * @param string $url
     * @param string|NULL $serverName
     * @return mixed
     */
    public static function getPath($url, $serverName = NULL)
    {
        if (empty($serverName)) {
            $serverName = $_SERVER['SERVER_NAME'];
        }

        if (!is_string($url)) {
            throw new InvalidArgumentException(__CLASS__ . ': Argument 1 must be string, ' . gettype($url) . ' given.');
        }

        if (!is_string($serverName)) {
            throw new InvalidArgumentException(__CLASS__ . ': Argument 2 must be an string, ' . gettype($serverName) . ' given.');
        }

        if (parse_url($url, PHP_URL_SCHEME)) {
            $normalizedUrl = $url;

        } elseif (Strings::startsWith($url, 'www.')) {
            $normalizedUrl = 'http://' . $url;

        } elseif (Strings::startsWith($url, '//')) {
            $normalizedUrl = 'http:' . $url;

        } elseif (Strings::startsWith($url, $serverName)) {
            $normalizedUrl = 'http://www.' . $url;

        } elseif (Strings::startsWith($url, '/')) {
            $normalizedUrl = 'http://www.example.com' . $url;

        } else {
            $normalizedUrl = 'http://www.example.com/' . $url;
        }

        if (filter_var($normalizedUrl, FILTER_VALIDATE_URL)) {
            $path = parse_url($normalizedUrl, PHP_URL_PATH);
            return Strings::endsWith($path, '/') ? $path : $path . '/';

        } else {
            return FALSE;
        }
    }
}
