<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\provider;

use Closure;
use faz\common\Debug;
use Generator;
use phuongaz\giftcode\components\code\Code;
use pocketmine\player\Player;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;

class Provider{

    CONST CREATE_TABLE = "table.init";
    CONST INSERT_DATA = "table.insert";
    CONST SELECT_DATA = "table.select";
    const UPDATE_DATA = "table.update";

    private DataConnector $dataConnector;

    public function __construct(DataConnector $dataConnector){
        $this->dataConnector = $dataConnector;
        Await::g2c($dataConnector->asyncGeneric(self::CREATE_TABLE));
    }

    public function awaitInsert(Player|string $player, array $codes) :Generator {
        yield $this->dataConnector->asyncInsert(self::INSERT_DATA, [
            "player_name" => $player instanceof Player ? strtolower($player->getName()) : $player,
            "code" => json_encode($codes),
            "used_code" => json_encode([])
        ]);
    }

    /**
     * @param Player|string $player
     * @param Closure|null $onSusses
     * @return Generator<Code[]>
     */
    public function awaitPlayerCodes(Player|string $player, ?Closure $onSuccess = null) : Generator{
        $rows = yield from $this->dataConnector->asyncSelect(self::SELECT_DATA, [
            "player_name" => $player instanceof Player ? strtolower($player->getName()) : $player
        ]);
        if(empty($rows)){
            yield $this->awaitInsert($player, []);
            return [];
        }
        $codes = json_decode($rows[0]["code"], true);
        if ($onSuccess !== null) {
            $onSuccess($codes);
        }

        return array_map(fn($code) => Code::fromArray($code), $codes);
    }

    /**
     * @param Player|string $player
     * @param Closure|null $onSusses
     * @return Generator<Code[]>
     */
    public function awaitUsedCodes(Player|string $player, Closure $onSuccess = null) : Generator{
        $rows = yield from $this->dataConnector->asyncSelect(self::SELECT_DATA, [
            "player_name" => $player instanceof Player ? strtolower($player->getName()) : $player
        ]);
        if(empty($rows)){
            return [];
        }
        $codes = json_decode($rows[0]["used_code"], true);
        if ($onSuccess !== null) {
            $onSuccess($codes);
        }

        return $codes;
    }

    public function awaitUpdate(Player|string $player, array $codes, ?array $usedCodes = null) : Generator{
        yield $this->dataConnector->asyncChange(self::UPDATE_DATA, [
            "player_name" => $player instanceof Player ? strtolower($player->getName()) : $player,
            "code" => json_encode($codes),
            "used_code" => json_encode($usedCodes ?? [])
        ]);
    }
}