<?php

namespace JDLX\DrawioConverter\JSON;

class Driver
{


    public function normalize($string)
    {
        $string = mb_strtolower($string);
        $string = preg_replace('`\W`', '_', $string);
        return $string;
    }

    public function escape($string) {
        return '`' . $string . '`';
    }
}
