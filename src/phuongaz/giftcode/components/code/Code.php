<?php

declare(strict_types=1);

namespace phuongaz\giftcode\components\code;

use faz\common\utils\Serializer;
use JsonException;
use phuongaz\giftcode\Loader;
use pocketmine\item\Item;

class Code {

    public function __construct(
        private string $code,
        private string $temp,
        private bool $rewardInventory,
        private array $items,
        private array $commands,
        private bool $secret,
        private int $maxLimit = 0,
        private int $limitUsed = 0,
    ){}

    public function getMaxLimit(): int{
        return $this->maxLimit;
    }

    public function getLimitUsed(): int{
        return $this->limitUsed;
    }
    public function setMaxLimit(int $maxLimit): void{
        $this->maxLimit = $maxLimit;
    }

    public function setLimitUsed(int $limitUsed): void{
        $this->limitUsed = $limitUsed;
    }

    public function isLimit(): bool{
        return $this->maxLimit !== 0;
    }

    public function isLimitUsed(): bool{
        if($this->maxLimit === 0) return false;
        return $this->limitUsed >= $this->maxLimit;
    }

    public function isSecret(): bool{
        return $this->secret;
    }

    public function getCode(): string{
        return $this->code;
    }

    public function getTemp(): string{
        return $this->temp;
    }

    public function getRewardInventory(): bool{
        return $this->rewardInventory;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array{
        return $this->items;
    }

    public function getCommands(): array{
        return $this->commands;
    }


    public function isExpired(): bool{

        if (empty($this->temp) || !($spec = \DateTime::createFromFormat("Y-m-d H:i:s", $this->temp))) {
            throw new \RuntimeException("Time format is invalid must be Y-m-d H:i:s");
        }

        $now = new \DateTime();

        return $now > $spec;
    }

    /**
     * @throws JsonException
     */
    public function save() : void {
        $codesConfig = Loader::getInstance()->getCodesConfig();
        $codesConfig->set($this->code, [
            "temp" => $this->temp,
            "rewardInventory" => $this->rewardInventory,
            "items" => (new Serializer())->encodeItems($this->items),
            "commands" => $this->commands,
            "secret" => $this->secret ? "true" : "false",
            "maxLimit" => $this->maxLimit,
            "limitUsed" => $this->limitUsed
        ]);
        $codesConfig->save();
    }

    /**
     * @throws JsonException
     */
    public function delete() : void {
        $codesConfig = Loader::getInstance()->getCodesConfig();
        $codesConfig->remove($this->code);
        $codesConfig->save();
    }

    public function toArray(): array{
        return [
            "code" => $this->code,
            "temp" => $this->temp,
            "rewardInventory" => $this->rewardInventory,
            "items" => (new Serializer())->encodeItems($this->items),
            "commands" => $this->commands,
            "secret" => $this->secret ? "true" : "false",
            "maxLimit" => $this->maxLimit,
            "limitUsed" => $this->limitUsed
        ];
    }

    public function toString(): string{
        return json_encode($this->toArray());
    }

    public static function fromString(string $string): self{
        $data = json_decode($string, true);
        return new self(
            $data["code"],
            $data["temp"],
            $data["rewardInventory"],
            (new Serializer())->decodeItems($data["items"]),
            $data["commands"],
            $data["secret"],
            $data["maxLimit"] ?? 0,
            $data["limitUsed"] ?? 0
        );
    }

    public static function fromArray(array $data): self {

        if(is_string($data["items"])) {
            $data["items"] = (new Serializer())->decodeItems($data["items"]);
        }

        if(is_string($data["secret"])) {
            $data["secret"] = $data["secret"] === "true";
        }

        return new self(
            $data["code"],
            $data["temp"],
            $data["rewardInventory"],
            $data["items"],
            $data["commands"],
            $data["secret"],
            $data["maxLimit"] ?? 0,
            $data["limitUsed"] ?? 0
        );
    }

    public static function fromConfig(string $code, array $data) :self {
        return new self(
            $code,
            $data["temp"],
            $data["rewardInventory"],
            (new Serializer())->decodeItems($data["items"]),
            $data["commands"],
            $data["secret"] === "true",
            $data["maxLimit"] ?? 0,
            $data["limitUsed"] ?? 0
        );
    }
}