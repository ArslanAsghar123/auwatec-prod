<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\String;

class StringAnalyzer
{
    /**
     * Checks if the $haystack string begins with the $needle string
     *
     * @param bool $caseSensitive
     */
    public function stringStartsWith(string $haystack, string $needle, $caseSensitive = false): bool
    {
        /** Return true, if the needle is empty */
        if (empty($needle)) {
            return true;
        }

        /** Checks if the string starts with the needle string */
        if (true === $caseSensitive) {
            /** Check case-sensitive */
            return str_starts_with($haystack, $needle);
        }

        /** Check case-insensitive */
        return 0 === strripos($haystack, $needle);
    }

    /**
     * Checks if the $haystack string ends with the $needle string
     *
     * @param bool $caseSensitive
     */
    public function stringEndsWith(string $haystack, string $needle, $caseSensitive = false): bool
    {
        /** Return true, if the needle is empty */
        if (empty($needle)) {
            return true;
        }

        /** Calculate the needle length */
        $needleLength = mb_strlen($needle);

        /** Get the end of the haystack with the same length */
        $haystackStringEnd = substr($haystack, $needleLength * -1);

        if (true === $caseSensitive) {
            /** Compare the to strings */
            return $haystackStringEnd === $needle;
        }

        /** Make a case-insensitive comparison */
        return strtolower($haystackStringEnd) === strtolower($needle);
    }


    /**
     * Checks if the $haystack string contains the $needle string
     *
     * @param bool $caseSensitive
     */
    public function stringContains(string $haystack, string $needle, $caseSensitive = false): bool
    {
        /** Return true, if the needle is empty */
        if (empty($needle)) {
            return true;
        }

        /** Checks if the string starts with the needle string */
        if (true === $caseSensitive) {
            /** Check case-sensitive */
            return str_contains($haystack, $needle);
        }

        /** Check case-insensitive */
        return false !== strripos($haystack, $needle);
    }
}
