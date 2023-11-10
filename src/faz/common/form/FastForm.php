<?php

declare(strict_types=1);

namespace faz\common\form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use pocketmine\player\Player;

class FastForm {

    public static function simpleNotice(Player $player, string $message, ?\Closure $onClose = null) {
        $player->sendForm(new CustomForm(
            "Notice",
            [
                new Label("message", $message)
            ],
            function (Player $player, CustomFormResponse $response) use ($onClose) {
                if($onClose !== null) {
                    $onClose($player, $response);
                }
            }
        ));
    }
}