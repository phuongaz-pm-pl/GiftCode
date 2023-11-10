<?php

declare(strict_types=1);

namespace phuongaz\giftcode;

use JsonException;
use phuongaz\giftcode\components\code\CodePool;
use phuongaz\giftcode\components\event\PlayerUseCodeEvent;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class EventHandler implements Listener {

    public function onJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();

        Await::f2c(function() use ($player) {
            $provider = Loader::getInstance()->getProvider();
            $codes = yield from $provider->awaitPlayerCodes($player->getName());
            $player->sendMessage("§aVocê tem §e" . count($codes) . " §acódigos!");
        });
    }

    /**
     * @throws JsonException
     */
    public function onUseCode(PlayerUseCodeEvent $event) :void {
        $player = $event->getPlayer();
        $code = $event->getCode();
        $items = $code->getItems();
        $commands = $code->getCommands();
        if(count($items) > 0){
            $inventory = $player->getInventory();
            foreach($items as $item){
                if($inventory->canAddItem($item)){
                    $inventory->addItem($item);
                }else{
                    $player->sendMessage("§cVocê não tem espaço suficiente em seu inventário!");
                    break;
                }
            }
        }
        $console = new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage());
        foreach($commands as $command){
            $player->getServer()->dispatchCommand($console, str_replace("{player}", $player->getName(), $command));
        }

        if($code->isLimit()) {
            $code->setLimitUsed($code->getLimitUsed() + 1);
            $code->save();
            CodePool::reloadCode($code);
        }
    }
}
