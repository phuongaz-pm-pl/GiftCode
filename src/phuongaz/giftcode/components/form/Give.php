<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\form;

use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Label;
use faz\common\Debug;
use Generator;
use faz\common\form\AsyncForm;
use phuongaz\giftcode\components\code\Code;
use phuongaz\giftcode\components\code\CodePool;
use phuongaz\giftcode\Loader;
use pocketmine\player\Player;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class Give extends AsyncForm {

    public function __construct(Player $player) {
        parent::__construct($player);
    }

    public function main(): Generator {
        $codes = array_map(fn($code) => $code->getCode(), CodePool::getCodes());
        if(count($codes) === 0) {
            return yield $this->custom("GiftCodes", [
                new Label("code", "Não há códigos disponíveis.")
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

            yield Loader::getInstance()->getProvider()->awaitPlayerCodes($data["player"], function (?array $codes) use ($data) {
                if(is_null($codes)) {
                    $provider = Loader::getInstance()->getProvider();
                    Await::g2c($provider->awaitInsert($data["player"], []));
                }
                /** @var Code $code */
                $code = $data["code"];
                $player = $data["player"];
                $provider = Loader::getInstance()->getProvider();
                if(!is_null($player)) {
                    $codes[] = $code->toArray();
                    $player->sendMessage("§aVocê recebeu o código <{$code->getCode()}>");
                    Await::g2c($provider->awaitUpdate($player->getName(), $codes));
                }
            });
        }
    }
}