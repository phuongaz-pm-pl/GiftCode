<?php

declare(strict_types=1);

namespace faz\common;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Common {

    public static function getConnector(PluginBase $pluginBase, array $sqlMap = ["sqlite" => "sqlite.sql", "mysql" => "mysql.sql"]) :DataConnector {
        $databaseConfig = $pluginBase->getConfig()->get("database");
        return libasynql::create($pluginBase, $databaseConfig, $sqlMap);
    }

    public static function getAllNamePlayers() : \Generator {
        $path = Server::getInstance()->getDataPath() . "players/";

        $playerFiles = glob($path . "*.dat");

        foreach ($playerFiles as $playerFile) {
            yield basename($playerFile, ".dat");
        }
    }
}