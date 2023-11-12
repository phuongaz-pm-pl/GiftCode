<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\form;

use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use faz\common\form\AsyncForm;
use Generator;
use JsonException;
use phuongaz\giftcode\components\code\Code;
use phuongaz\giftcode\components\code\CodePool;
use phuongaz\giftcode\components\event\PlayerUseCodeEvent;
use phuongaz\giftcode\Loader;
use phuongaz\giftcode\utils\CodeUtils;
use pocketmine\player\Player;

class ListCode extends AsyncForm {

    public function __construct(Player $player) {
        parent::__construct($player);
    }

    /**
     * @throws JsonException
     */
    public function main(): Generator {
        $provider = Loader::getInstance()->getProvider();
        /** @var Code[] $codes */
        $codes = yield from $provider->awaitPlayerCodes($this->getPlayer());

        $elements = [];
        $elements[] = new Input("code_input", "Digite o código", "GC");
        $index = 0;
        foreach ($codes as $code) {
            $expires = ($code->getTemp() == "null") ? "" : " [Expira: " . $code->getTemp() . "]";
            $elements[] = new Label("code" . $index, $code->getCode() . $expires );
            $index++;
        }

        $codeResponse = yield from $this->custom(
            "Giftcode",
            $elements
        );

        if ($codeResponse != null) {

            $codeData = $codeResponse->getAll();
            $code = $codeData["code_input"] ?? "";

            /** @var Code[] $usedCodes */
            $usedCodes = yield from $provider->awaitUsedCodes($this->getPlayer());

            $code = CodePool::getCode($code);

            if($code === null) {
                return yield $this->custom("Giftcode", [
                    new Label("code", "Este código não existe.")
                ]);
            }

            if(!in_array($code, $codes)) {
                if(!$code->isSecret()) {
                    if(in_array($code->getCode(), $usedCodes)) {
                        yield $this->custom("Giftcode", [
                            new Label("code", "Você utilizou este código.")
                        ]);
                        return yield from $this->main();
                    }
                }else{
                    return yield $this->custom("Giftcode", [
                        new Label("code", "O código não existe")
                    ]);
                }
            }

            if(in_array($code->getCode(), $usedCodes)) {
                yield $this->custom("Giftcode", [
                    new Label("code", "Você utilizou este código.")
                ]);
                return yield from $this->main();
            }

            $usedCodes[] = $code->getCode();

            $codeIndex = CodeUtils::findIndexCodeInArray($code->getCode(), $codes);

            if($codeIndex !== -1) {
                unset($codes[$codeIndex]);
            }

            if($code->isLimitUsed()) {
                yield $this->custom("Giftcode", [
                    new Label("code", "Este código já foi utilizado.")
                ]);
                return yield $provider->awaitUpdate($this->getPlayer(), $codes, $usedCodes);
            }

            if($code->isExpired()) {
                yield $this->custom("Giftcode", [
                    new Label("code", "Este código expirou.")
                ]);
                CodePool::removeCode($code->getCode());
                $code->delete();
                return yield $provider->awaitUpdate($this->getPlayer(), $codes, $usedCodes);
            }

            yield $provider->awaitUpdate($this->getPlayer(), $codes, $usedCodes);

            $event = new PlayerUseCodeEvent($this->getPlayer(), $code);
            $event->call();

            return yield $this->custom("Giftcode", [
                new Label("code", "Você recebeu este código."),
                new Label("code_remain", "Você tem " . count($codes) . " códigos restantes.")
            ]);
        }
    }
}