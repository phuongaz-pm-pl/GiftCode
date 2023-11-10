<?php

namespace faz\common\async;

use Closure;
use faz\common\Common;
use Generator;
use SOFe\AwaitGenerator\Await;

class Func {

    /**
     * Function handle with name all players in server
     *
     * @param Closure $resolve
     * @param Closure $onSuccess
     * @return Generator
     */
    public static function handleWithNamePlayers(Closure $callable, ?Closure $onSuccess = null) : Generator {
        $playersCount = yield Await::promise(function (Closure $resolve) use ($callable) {
            $players = Common::getAllNamePlayers();
            $count = 0;
            foreach ($players as $player) {
                $callable($player);
                $count++;
            }
            $resolve($count);
        });
        $onSuccess ?? $onSuccess($playersCount);
    }
}