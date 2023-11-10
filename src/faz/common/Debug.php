<?php

declare(strict_types=1);

namespace faz\common;

class Debug {

    //Dump without Generator function
    public static function dump($obj): void {
        ob_start();
        var_dump($obj);
        $dump = ob_get_clean();
        echo $dump;
    }

    public static function dumpF(callable $f): void {
        ob_start();
        $f();
        $dump = ob_get_clean();
        echo $dump;
    }
}