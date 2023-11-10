<?php

declare(strict_types=1);

namespace faz\common\database;

use Generator;
use poggit\libasynql\DataConnector;

abstract class GeneralDatabase {
    use SQLTrait;

    public function __construct(private DataConnector $connector) {
        self::setConnector($this->connector);
    }

    public function getConnector() : DataConnector {
        return $this->connector;
    }

    public function has(string $queryName, string|int $key, string|int $value, ?\Closure $callback = null) : Generator {
        return self::has($queryName, $key, $value, $callback);
    }

}