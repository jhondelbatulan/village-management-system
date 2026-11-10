<?php

if (!function_exists('mb_split')) {
    function mb_split($pattern, $string, $limit = -1)
    {
        $result = preg_split('/' . str_replace('/', '\/', $pattern) . '/u', $string, $limit);

        return $result === false ? false : $result;
    }
}
