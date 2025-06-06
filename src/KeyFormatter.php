<?php

namespace Actinity\SignedUrls;

class KeyFormatter
{
    public static function publicFromString(string $string): string
    {
        return self::fromString($string);
    }

    public static function privateFromString(string $string): string
    {
        return self::fromString($string, true);
    }

    private static function fromString(string $string, bool $is_private = false): string
    {
        if (substr($string, 0, 12) !== '-----BEGIN P') {
            $string = trim($string);
            $string = wordwrap($string, 64, "\n", true);
            $pp = $is_private ? 'PRIVATE' : 'PUBLIC';
            $string = "-----BEGIN {$pp} KEY-----\n".$string;
            $string .= "\n-----END {$pp} KEY-----";
        }

        return $string;
    }

    public static function toString(string $key): string
    {
        $lines = explode("\n", trim($key));
        if (count($lines) && substr($lines[0], 0, 10) === '-----BEGIN') {
            $lines = array_slice($lines, 1, -1);
        }
        $lines = array_map('trim', $lines);

        return implode('', $lines);
    }
}
