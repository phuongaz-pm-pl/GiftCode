<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\code;

use WeakMap;

class CodePool {

    /** @var array<string, Code> $codes */
    public static array $codes = [];

    public static function getCode(string $code) :?Code {
        return self::$codes[$code] ?? null;
    }

    public static function addCode(Code $code) :void {
        self::$codes[$code->getCode()] = $code;
    }

    public static function removeCode(string $code) : void {
        unset(self::$codes[$code]);
    }

    public static function hasCode(string $code) : bool {
        return isset(self::$codes[$code]);
    }

    public static function getCodes() : array {
        return self::$codes;
    }

    public static function clear() : void {
        self::$codes = [];
    }

    public static function reloadCode(Code $code): void {
        self::removeCode($code->getCode());
        self::addCode($code);
    }
}