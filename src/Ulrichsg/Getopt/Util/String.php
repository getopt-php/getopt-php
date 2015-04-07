<?php

namespace Ulrichsg\Getopt\Util;

class String
{
    public static function at($string, $pos)
    {
        if ($pos >= self::length($string)) {
            return null;
        }
        return $string[$pos];
    }

    public static function length($string)
    {
        return mb_strlen($string, "UTF-8");
    }

    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, "UTF-8");
    }

    public static function startsWith($string, $prefix)
    {
        return self::substr($string, 0, self::length($prefix)) === $prefix;
    }

    /**
     * Split the string into individual characters,
     *
     * @param string $string string to split
     * @return array
     */
    public static function split($string)
    {
        $result = array();
        for ($i = 0; $i < self::length($string); ++$i) {
            $result[] = mb_substr($string, $i, 1, "UTF-8");
        }
        return $result;
    }

    public static function contains($string, $substr)
    {
        return mb_strpos($string, $substr, null, "UTF-8");
    }

    public static function isSpaceOrEnd($string, $pos)
    {
        if ($pos >= self::length($string)) {
            return true;
        }
        return ctype_space($string[$pos]);
    }
}
