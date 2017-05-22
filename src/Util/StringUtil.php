<?php

namespace Tequila\MongoDB\ODM\Util;

class StringUtil
{
    public static function camelize(string $string, bool $camelizeFirstChar = true)
    {
        $string = str_replace('-', '', ucwords($string, '-'));

        return $camelizeFirstChar ? $string : lcfirst($string);
    }
}
