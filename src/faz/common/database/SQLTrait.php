<?php

declare(strict_types=1);

namespace faz\common\database;

use Generator;
use pocketmine\utils\Utils;
use poggit\libasynql\DataConnector;

trait SQLTrait {

    private static ?DataConnector $connector = null;

    public static function setConnector(DataConnector $connector) : void {
        self::$connector = $connector;
    }

    public static function getConnector() : ?DataConnector {
        return self::$connector;
    }

    public static function has(string $queryName, string|int $key, string|int $value, ?\Closure $callback = null) : Generator {
        $rows = yield from self::getConnector()->asyncSelect($queryName, [$key => $value]);

        $hasData = !empty($rows);

        if(!is_null($callback)) {
            Utils::validateCallableSignature(function(bool $hasData){}, $callback);
            $callback($hasData);
        }

        return $hasData;
    }
}