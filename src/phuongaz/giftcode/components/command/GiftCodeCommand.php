<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\command;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use phuongaz\giftcode\components\command\sub\CreateSub;
use phuongaz\giftcode\components\command\sub\GiveSub;
use phuongaz\giftcode\components\command\sub\ListSub;
use pocketmine\command\CommandSender;

class GiftCodeCommand extends BaseCommand {

    const PERMISSION = "giftcode.command";

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void {
        $this->setPermission(self::PERMISSION);
        $this->registerSubCommand(new CreateSub("create", "Create giftcode", ["c"]));
        $this->registerSubCommand(new GiveSub("give", "Give giftcode", ["g"]));
        $this->registerSubCommand(new ListSub("list", "List giftcode", ["l"]));
        $this->setPermission(self::PERMISSION);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $this->sendUsage();
    }

    public function getPermission(): string {
        return self::PERMISSION;
    }
}