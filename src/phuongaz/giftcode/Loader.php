<?php

declare(strict_types=1);

namespace phuongaz\giftcode;

use phuongaz\giftcode\components\code\Code;
use phuongaz\giftcode\components\code\CodePool;
use phuongaz\giftcode\components\command\GiftCodeCommand;
use phuongaz\giftcode\components\provider\Provider;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase
{
    use SingletonTrait;

    private DataConnector $dataConnector;
    private Config $codesConfig;

    protected function onLoad(): void
    {
       self::setInstance($this);
    }

    protected function onEnable(): void
    {
        $this->codesConfig = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
        $this->saveDefaultConfig();
        $this->dataConnector = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);

        $this->getServer()->getCommandMap()->register("giftcode", new GiftCodeCommand(
            $this, "giftcode", "Giftcode command", ["gc"]
        ));
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);

        $this->loadCodes();
    }

    public function loadCodes() : void {
        $this->codesConfig->reload();
        $codes = $this->codesConfig->getAll();
        foreach($codes as $code => $data)  {
            $code = Code::fromConfig($code, $data);
            CodePool::addCode($code);
            $this->getLogger()->info("Loaded code " . $code->getCode() . " with " . count($code->getItems()) . " items and " . count($code->getCommands()) . " commands");
        }
    }

    public function getProvider(): Provider {
        return new Provider($this->dataConnector);
    }

    public function getCodesConfig(): Config {
        return $this->codesConfig;
    }

    /**
     * @throws \JsonException
     */
    protected function onDisable() :void {
        if(isset($this->dataConnector)) {
            $this->dataConnector->waitAll();
        }
        foreach(CodePool::getCodes() as $code) {
            if($code->isExpired()) {
                $code->delete();
            }
        }
    }

}