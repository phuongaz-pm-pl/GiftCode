<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\command\sub;

use CortexPE\Commando\BaseSubCommand;
use phuongaz\giftcode\components\form\Give;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class GiveSub extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission("giftcode.command.give");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if(!$sender instanceof Player) return;
        (new Give($sender))->send();
    }
}