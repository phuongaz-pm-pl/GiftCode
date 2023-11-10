<?php

declare(strict_types=1);

namespace faz\common\form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\ModalForm;
use Generator;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

abstract class AsyncForm {

    public function __construct(private Player $player) {}

    public function getPlayer() : Player {
        return $this->player;
    }

    abstract public function main() : Generator;

    public function send() : void {
        Await::f2c(fn() => yield $this->main());
    }

    public function custom(string $title, array $elements): Generator {
        $f = yield Await::RESOLVE;
        $this->getPlayer()->sendForm(new CustomForm(
            $title, $elements,
            function (Player $player, CustomFormResponse $result) use ($f): void {
                $f($result);
            },
            function (Player $player) use ($f): void {
                $f(null);
            }
        ));
        return yield Await::ONCE;
    }

    public function menu(string $title, string $text, array $options): Generator {
        return yield from Await::promise(function($resolve) use ($options, $text, $title) {
            $this->getPlayer()->sendForm(new MenuForm(
                $title, $text, $options,
                function (Player $player, int $selectedOption) use ($resolve) : void {
                    $resolve($selectedOption);
                },
                function (Player $player) use ($resolve) : void {
                    $resolve(null);
                }
            ));
        });
    }

    public function modal(string $title, string $text, string $yesButtonText = "gui.yes", string $noButtonText = "gui.no"): Generator {
        $f = yield Await::RESOLVE;
        $this->getPlayer()->sendForm(new ModalForm(
            $title, $text,
            function (Player $player, bool $choice) use ($f): void {
                $f($choice);
            },
            $yesButtonText, $noButtonText
        ));
        return yield Await::ONCE;
    }
}
