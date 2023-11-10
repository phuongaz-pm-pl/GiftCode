<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\form;

use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use faz\common\form\AsyncForm;
use Generator;
use JsonException;
use phuongaz\giftcode\components\code\Code;
use phuongaz\giftcode\components\code\CodePool;
use phuongaz\giftcode\utils\Utils;
use pocketmine\player\Player;

class Create extends AsyncForm {

    public function __construct(Player $player) {
        parent::__construct($player);
    }

    /**
     * @throws JsonException
     */
    public function main(): Generator {
        $randCode = Utils::randomString("GC-", 8);
        $elements = [
            new Input("code", "Code", $randCode, $randCode),
            new Input("time", "Time:", "1d 4h", "1d 4h"),
            new Input("command", "Commands {player}", "command1;command2;command3"),
            new Toggle("inventory", "Inventory"),
            new Toggle("secret", "Público/Secreto"),
            new Input("maxLimit", "Limite Máximo", "")
        ];

        $createResponse = yield from $this->custom(
            "Giftcode",
            $elements
        );

        if($createResponse != null) {
            $items = $this->getPlayer()->getInventory()->getContents();
            $codeData = $createResponse->getAll();
            if(!empty($codeData["maxLimit"])) {
                if(!is_numeric($codeData["maxLimit"])) {
                    yield $this->custom("Giftcode", [new Label("code", "O limite máximo deve ser um número")]);
                    return yield from $this->main();
                }
            }
            $code = Code::fromArray([
                "code" => $codeData["code"] ?? $randCode,
                "temp" => Utils::getExpireTime($codeData["time"]) ?? "null",
                "rewardInventory" => $codeData["inventory"],
                "items" => $items,
                "commands" => explode(";", $codeData["command"]) ?? [],
                "secret" => $codeData["secret"],
                "maxLimit" => (int)$codeData["maxLimit"] ?? -1,
                "limitUsed" => 0
            ]);
            $code->save();
            CodePool::addCode($code);
            $this->getPlayer()->sendMessage("§aGiftcode {$code->getCode()} criado");
        }
    }
}