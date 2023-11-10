<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\event;

use phuongaz\giftcode\components\code\Code;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerUseCodeEvent extends PlayerEvent
{
    private Code $code;

    public function __construct(Player $player, Code $code)
    {
        $this->player = $player;
        $this->code = $code;
    }

    public function getCode(): Code
    {
        return $this->code;
    }
}