<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\form;

use dktapps\pmforms\element\Dropdown;
use Generator;
use faz\common\form\AsyncForm;
use phuongaz\giftcode\components\code\CodePool;
use phuongaz\giftcode\Loader;
use pocketmine\player\Player;
use pocketmine\Server;

class Give extends AsyncForm {

    public function __construct(Player $player) {
        parent::__construct($player);
    }

    public function main(): Generator {
        $codes = array_map(fn($code) => $code->getCode(), CodePool::getCodes());
        if(count($codes) === 0) {
            return yield $this->custom("GiftCodes", [
                "§cNão há códigos disponíveis."
            ]);
        }

        $onlinePlayers = array_map(fn($player) => $player->getName(), Server::getInstance()->getOnlinePlayers());
        $elements = [
            new Dropdown("codeIndex", "Codes", $codes),
            new Dropdown("player", "Players", $onlinePlayers),
            //new Toggle("all", "All players in server", false)
        ];

        $giveResponse = yield from $this->custom(
            "GiftCodes",
            $elements
        );

        if($giveResponse != null) {
            $data = $giveResponse->getAll();
            $data["code"] = array_values(CodePool::getCodes())[$data["codeIndex"]];
            $data["player"] = array_values(Server::getInstance()->getOnlinePlayers())[$data["player"]];
            yield $this->executeAction($data);
        }
    }

    public function executeAction(array $giveData) : Generator {
        $code = $giveData["code"];
        $player = $giveData["player"];

        $provider = Loader::getInstance()->getProvider();
        if(!is_null($player)) {
            $codes = yield from $provider->awaitPlayerCodes($player->getName());
            $codes[] = $code->toArray();
            $player->sendMessage("§aVocê recebeu o código <{$code->getCode()}>");
            yield $provider->awaitUpdate($player->getName(), $codes);
            return;
        }

//        if($giveData["all"]) {
//            $amount = yield Await::promise(function(Closure $resolve) use ($provider, $code) {
//                $giveFunc = function (string $playerName) use ($provider, $code) {
//                    $codes = yield from $provider->awaitPlayerCodes($playerName);
//                    $codes[] = $code->toArray();
//                    yield $provider->awaitUpdate($playerName, $codes);
//                };
//                yield Func::handleWithNamePlayers($giveFunc, fn($amount) => $resolve($amount));
//            });
//            yield $this->custom("GiftCodes", [
//                "§a{$amount} players received code <{$code->getCode()}>"
//            ]);
//            yield $this->main();
//        }
    }
}
