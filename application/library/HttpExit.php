<?php

class HttpExit extends RuntimeException
{

    public static function now($msg, $code)
    {
        throw new self($msg, $code);
    }

    public static function now404($msg)
    {
        self::now($msg , 404);
    }

}